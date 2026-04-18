<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * MAIN APPLICATION TABLE
         */
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
             
            // Education entry level
            $table->enum('entry_level', ['CSEE', 'ACSEE', 'Diploma', 'Degree', 'Mature'])->default('CSEE');
            
            // Application cycle
            $table->foreignId('academic_year_id')->constrained();
            $table->enum('intake', ['March', 'September'])->default('March');
            
            // Status flow
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'shortlisted',
                'interview_invited',
                'interview_attended',
                'accepted',
                'provisionally_accepted',
                'rejected',
                'withdrawn'
            ])->default('draft');
            
            // Application steps completion
            $table->boolean('step_personal_completed')->default(false);
            $table->boolean('step_contact_completed')->default(false);
            $table->boolean('step_next_of_kin_completed')->default(false);
            $table->boolean('step_academic_completed')->default(false);
            $table->boolean('step_programs_completed')->default(false);
            $table->boolean('step_documents_completed')->default(false);
            $table->boolean('step_declaration_completed')->default(false);
            
            // Submission & review
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->text('review_notes')->nullable();
            
            // Payment
            $table->boolean('is_paid')->default(false);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('payment_reference')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->boolean('is_free_application')->default(true);
            $table->string('fee_waiver_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['academic_year_id', 'intake']);
            $table->index('status');
            $table->index('application_number');
        });

        /**
         * PERSONAL INFORMATION TABLE
         */
        Schema::create('application_personal_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            
            // Name
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('surname')->nullable();
            
            // Personal details
            $table->enum('gender', ['Male', 'Female']);
            $table->date('date_of_birth');
            $table->string('place_of_birth');
            $table->string('country_of_birth')->default('Tanzania');
            $table->string('nationality')->default('Tanzanian');
            $table->string('citizenship_status')->default('Citizen by Birth');
            
            // Identification
            $table->string('national_id')->nullable()->unique();
            $table->string('passport_number')->nullable()->unique();
            $table->string('birth_certificate_number')->nullable();
            
            // Personal status
            $table->enum('marital_status', ['Single', 'Married', 'Divorced', 'Widowed'])->default('Single');
            $table->integer('dependents')->default(0);
            $table->string('religion')->nullable();
            $table->string('denomination')->nullable();
            
            // Disability
            $table->boolean('has_disability')->default(false);
            $table->enum('disability_type', ['None', 'Physical', 'Visual', 'Hearing', 'Speech', 'Mental', 'Multiple', 'Other'])->default('None');
            $table->text('disability_details')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('national_id');
            $table->index('passport_number');
        });

        /**
         * CONTACT INFORMATION TABLE
         */
        Schema::create('application_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            
            // Contact details
            $table->string('phone');
            $table->string('phone_alternative')->nullable();
            $table->string('email')->nullable();
            $table->string('email_alternative')->nullable();
            
            // Current address
            $table->string('region');
            $table->string('district');
            $table->string('ward');
            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('physical_address');
            $table->string('postal_address')->nullable();
            $table->string('postal_code')->nullable();
            
            // Permanent address
            $table->boolean('permanent_same_as_current')->default(true);
            $table->string('permanent_region')->nullable();
            $table->string('permanent_district')->nullable();
            $table->string('permanent_ward')->nullable();
            $table->string('permanent_address')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('phone');
            $table->index('region');
        });

        /**
         * NEXT OF KIN TABLE
         */
        Schema::create('application_next_of_kins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            
            // Primary guardian
            $table->string('guardian_name');
            $table->enum('relationship', ['Father', 'Mother', 'Guardian', 'Spouse', 'Sibling', 'Other'])->default('Father');
            $table->string('guardian_phone');
            $table->string('guardian_email')->nullable();
            $table->string('guardian_occupation')->nullable();
            $table->string('guardian_address');
            
            // Alternative contact
            $table->string('alternative_contact_name')->nullable();
            $table->string('alternative_contact_phone')->nullable();
            $table->enum('alternative_relationship', ['Father', 'Mother', 'Guardian', 'Spouse', 'Sibling', 'Other'])->nullable();
            
            // Emergency contact
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->string('emergency_contact_relationship')->default('Relative');
            
            $table->timestamps();
        });

        /**
         * ACADEMIC BACKGROUND TABLE
         */
        Schema::create('application_academics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            
            // CSEE (Form IV)
            $table->string('csee_school');
            $table->string('csee_school_address')->nullable();
            $table->string('csee_index_number');
            $table->year('csee_year');
            $table->enum('csee_division', ['I', 'II', 'III', 'IV']);
            $table->integer('csee_points')->nullable();
            $table->string('csee_examination_body')->default('NECTA');
            
            // ACSEE (Form VI) - Optional
            $table->string('acsee_school')->nullable();
            $table->string('acsee_school_address')->nullable();
            $table->string('acsee_index_number')->nullable();
            $table->year('acsee_year')->nullable();
            $table->integer('acsee_principal_passes')->nullable();
            $table->string('acsee_combination')->nullable();
            $table->string('acsee_examination_body')->default('NECTA');
            
            // Diploma (Optional)
            $table->string('diploma_institution')->nullable();
            $table->string('diploma_programme')->nullable();
            $table->year('diploma_year')->nullable();
            $table->string('diploma_class')->nullable();
            $table->decimal('diploma_gpa', 3, 2)->nullable();
            
            // Degree (Optional)
            $table->string('degree_institution')->nullable();
            $table->string('degree_programme')->nullable();
            $table->year('degree_year')->nullable();
            $table->string('degree_class')->nullable();
            $table->decimal('degree_gpa', 3, 2)->nullable();
            
            $table->timestamps();
        });

        /**
         * O-LEVEL SUBJECTS TABLE
         */
        Schema::create('application_olevel_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_academic_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->string('grade'); // A, B, C, D, E, F
            $table->integer('points')->nullable(); // A=1, B=2, etc
            $table->timestamps();
            
            $table->index('subject');
        });

        /**
         * A-LEVEL SUBJECTS TABLE
         */
        Schema::create('application_alevel_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_academic_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->string('grade'); // A, B, C, D, E, S
            $table->integer('points')->nullable();
            $table->timestamps();
            
            $table->index('subject');
        });

        /**
         * PROGRAM CHOICES TABLE
         */
        Schema::create('application_program_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            
            // Choices
            $table->foreignId('first_choice_program_id')->constrained('programmes');
            $table->foreignId('second_choice_program_id')->nullable()->constrained('programmes');
            $table->foreignId('third_choice_program_id')->nullable()->constrained('programmes');
            
            // Preferences
            $table->enum('study_mode', ['Full Time', 'Part Time', 'Evening', 'Weekend'])->default('Full Time');
            $table->enum('study_type', ['Direct', 'Equivalent', 'Mature Age'])->default('Direct');
            
            // Sponsorship
            $table->enum('sponsorship', ['Private', 'Government', 'Sponsor', 'Loan', 'Other'])->default('Private');
            $table->string('sponsor_name')->nullable();
            $table->string('sponsor_phone')->nullable();
            $table->string('sponsor_address')->nullable();
            
            // Information source
            $table->enum('information_source', [
                'Newspaper',
                'Radio',
                'TV',
                'Website',
                'Social Media',
                'Friend/Family',
                'School Visit',
                'Education Fair',
                'Other'
            ])->nullable();
            $table->string('information_source_other')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('first_choice_program_id');
            $table->index('second_choice_program_id');
            $table->index('third_choice_program_id');
        });

        /**
         * DOCUMENTS TABLE
         */
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            
            $table->enum('document_type', [
                'Birth Certificate',
                'Form IV Certificate',
                'Form VI Certificate',
                'Diploma Certificate',
                'Degree Certificate',
                'Transcript',
                'Passport Photo',
                'Passport Copy',
                'National ID',
                'Recommendation Letter',
                'Personal Statement',
                'CV',
                'Other'
            ]);
            
            $table->string('document_name');
            $table->string('file_path');
            $table->string('file_size')->nullable();
            $table->string('file_type')->nullable();
            
            // Verification
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('verification_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('document_type');
            $table->index('verification_status');
        });

        /**
         * DECLARATION TABLE
         */
        Schema::create('application_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            
            $table->boolean('confirm_information')->default(false);
            $table->boolean('accept_terms')->default(false);
            $table->boolean('confirm_documents')->default(false);
            $table->boolean('allow_data_sharing')->default(false);
            
            $table->timestamp('declared_at')->nullable();
            $table->string('signature_path')->nullable(); // Uploaded signature
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_declarations');
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('application_program_choices');
        Schema::dropIfExists('application_alevel_subjects');
        Schema::dropIfExists('application_olevel_subjects');
        Schema::dropIfExists('application_academics');
        Schema::dropIfExists('application_next_of_kins');
        Schema::dropIfExists('application_contacts');
        Schema::dropIfExists('application_personal_infos');
        Schema::dropIfExists('applications');
    }
};