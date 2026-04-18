<?php

namespace App\Services;

use App\Models\Result;
use App\Models\GradingSystem;
use App\Models\ResultApproval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ResultService
{
    public function createResult(array $data): Result
    {
        // Validate that student is registered for the module in that academic year & semester
        $registered = DB::table('course_registrations')
            ->where('student_id', $data['student_id'])
            ->where('module_id', $data['module_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('semester', $data['semester'])
            ->exists();

        if (!$registered) {
            throw new \Exception('Student is not registered for this module in the given semester.');
        }

        // Check for duplicate (current version)
        $existing = Result::where('student_id', $data['student_id'])
            ->where('module_id', $data['module_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('semester', $data['semester'])
            ->where('is_current', true)
            ->first();

        if ($existing && $existing->status !== 'draft') {
            throw new \Exception('Result already exists and cannot be modified.');
        }

        return DB::transaction(function () use ($data, $existing) {
            if ($existing) {
                // Create new version
                $new = $existing->replicate();
                $new->version = $existing->version + 1;
                $new->is_current = true;
                $new->fill($data);
                $new->save();

                $existing->is_current = false;
                $existing->save();

                return $new;
            } else {
                $result = new Result($data);
                $result->version = 1;
                $result->is_current = true;
                $result->save();
                return $result;
            }
        });
    }

    public function lock(Result $result)
    {
        if (!$result->canBeEdited()) {
            throw new \Exception('Result cannot be locked.');
        }
        $result->lock(Auth::user());
    }

    public function unlock(Result $result)
    {
        $result->unlock();
    }

    public function submitToHod(Result $result)
    {
        if ($result->status !== 'draft') {
            throw new \Exception('Only draft results can be submitted.');
        }

        DB::transaction(function () use ($result) {
            $oldStatus = $result->status;
            $result->status = 'pending_hod';
            $result->submitted_by = Auth::id();
            $result->submitted_at = now();
            $result->save();

            ResultApproval::create([
                'result_id' => $result->id,
                'user_id' => Auth::id(),
                'action' => 'submit',
                'status_from' => $oldStatus,
                'status_to' => 'pending_hod',
                'comments' => null,
            ]);

            // Fire event
            event(new \App\Events\ResultSubmitted($result));
        });
    }

    public function approve(Result $result, $comments = null)
    {
        if ($result->status !== 'pending_hod' && $result->status !== 'pending_academic') {
            throw new \Exception('Result is not in a state that can be approved.');
        }

        $nextStatus = match ($result->status) {
            'pending_hod' => 'pending_academic',
            'pending_academic' => 'pending_principal',
            default => throw new \Exception('Invalid status for approval'),
        };

        DB::transaction(function () use ($result, $comments, $nextStatus) {
            $oldStatus = $result->status;
            $result->status = $nextStatus;
            $result->approved_by = Auth::id();
            $result->approved_at = now();
            $result->save();

            ResultApproval::create([
                'result_id' => $result->id,
                'user_id' => Auth::id(),
                'action' => 'approve',
                'status_from' => $oldStatus,
                'status_to' => $nextStatus,
                'comments' => $comments,
            ]);

            event(new \App\Events\ResultApproved($result));
        });
    }

    public function reject(Result $result, $comments)
    {
        if ($result->status !== 'pending_hod') {
            throw new \Exception('Only results pending HOD can be rejected.');
        }

        DB::transaction(function () use ($result, $comments) {
            $oldStatus = $result->status;
            $result->status = 'draft';
            $result->remarks = $comments;
            $result->save();

            ResultApproval::create([
                'result_id' => $result->id,
                'user_id' => Auth::id(),
                'action' => 'reject',
                'status_from' => $oldStatus,
                'status_to' => 'draft',
                'comments' => $comments,
            ]);

            event(new \App\Events\ResultRejected($result));
        });
    }

    public function forwardToPrincipal(Result $result)
    {
        if ($result->status !== 'pending_academic') {
            throw new \Exception('Result must be pending academic to forward.');
        }

        DB::transaction(function () use ($result) {
            $oldStatus = $result->status;
            $result->status = 'pending_principal';
            $result->save();

            ResultApproval::create([
                'result_id' => $result->id,
                'user_id' => Auth::id(),
                'action' => 'forward',
                'status_from' => $oldStatus,
                'status_to' => 'pending_principal',
                'comments' => null,
            ]);

            event(new \App\Events\ResultForwarded($result));
        });
    }

    public function publish(Result $result)
    {
        if ($result->status !== 'pending_principal') {
            throw new \Exception('Only results pending principal can be published.');
        }

        DB::transaction(function () use ($result) {
            $oldStatus = $result->status;
            $result->status = 'published';
            $result->approved_by = Auth::id();
            $result->approved_at = now();
            $result->save();

            ResultApproval::create([
                'result_id' => $result->id,
                'user_id' => Auth::id(),
                'action' => 'publish',
                'status_from' => $oldStatus,
                'status_to' => 'published',
                'comments' => null,
            ]);

            event(new \App\Events\ResultPublished($result));
        });
    }
}