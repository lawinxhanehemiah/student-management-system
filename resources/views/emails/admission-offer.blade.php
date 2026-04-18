<!DOCTYPE html>
<html>
<head>
    <title>Admission Offer Letter - St. Maximillian College</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #dc3545;  /* Red background */
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header h2, .header h3 {
            margin: 0;
            padding: 5px 0;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .content {
            background: #fff;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .footer {
            background: #f5f5f5;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            padding: 12px 25px;
            background: #dc3545;  /* Red button */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
            font-weight: bold;
        }
        .button:hover {
            background: #c82333;
        }
        .badge {
            background: #28a745;
            color: white;
            padding: 5px 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .document-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #dc3545;
        }
        .document-list ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .document-list li {
            margin: 8px 0;
        }
        .fee-structure {
            background: #e8f4f8;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
        }
        .warning {
            color: #dc3545;
            font-size: 12px;
            margin-top: 10px;
        }
        .contact-info {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ url('/assets/images/header.jpg') }}" alt="St. Maximillian College Logo" class="logo" />
            <h3>Admission Offer Letter</h3>
        </div>
        
        <div class="content">
            <p>Dear <strong>{{ $name }}</strong>,</p>
            
            <p>Congratulations! <span class="badge">OFFERED</span></p>
            
            <p>We are pleased to inform you that you have been selected for <strong>{{ $programme }}</strong> programme at St. Maximillian College for the <strong>{{ $intake }}</strong> intake.</p>
            
            <p><strong>Your Application Number:</strong> {{ $application_number }}</p>
            <p><strong>Confirmation Code:</strong> {{ $confirmation_code ?? 'N/A' }}</p>
            
            <hr>
            
            <!-- Documents Required Section -->
            <h4 style="color: #dc3545;">📋 Documents Required for Registration:</h4>
            <div class="document-list">
                <ul>
                    <li><strong>📸 Passport Size Photos:</strong> 3 copies (recent, with white background)</li>
                    <li><strong>🎓 Academic Certificates:</strong> Original certificates and copies</li>
                    <li><strong>📄 Birth Certificate:</strong> Copy of birth certificate or affidavit</li>
                    <li><strong>🏥 Medical Examination:</strong> Recent medical report from recognized hospital</li>
                </ul>
            </div>
            
            <!-- Fee Structure Download Link -->
            <div class="fee-structure">
                <p><strong>💰 Download Fee Structure</strong></p>
                <p>Click the button below to download the complete fee structure including:</p>
                <ul style="text-align: left;">
                    <li>Tuition fees per semester/year</li>
                    <li>Registration and examination fees</li>
                    <li>Accommodation and meals (if applicable)</li>
                    <li>Other charges and payment schedule</li>
                </ul>
                <a href="{{ $fee_structure_url ?? '#' }}" class="button"> DOWNLOAD FEE STRUCTURE</a>
                @if(empty($fee_structure_url))
                <p class="warning">* Fee structure link will be provided upon request. Please contact admission office.</p>
                @endif
            </div>
            
            <div class="contact-info">
                <h4 style="margin-top: 0;">📞 Contact Information:</h4>
                <p><strong>Admission Office Hours:</strong> Monday-Friday, 8:00 AM - 5:00 PM</p>
                <p><strong>Phone:</strong> +255 623 053 846</p>
                <p><strong>Email:</strong> stmaximilliancollege@info.ac.tz</p>
                <p><strong>Website:</strong> www.stmaxcollege.ac.tz</p>
            </div>
            
            <p>For any inquiries or assistance, please don't hesitate to contact us. Our team is ready to help you with the registration process.</p>
            
            <p>We look forward to welcoming you to St. Maximillian College and wish you success in your academic journey!</p>
            
            <p>Yours sincerely,<br>
            <strong>Admission Office</strong><br>
            St. Maximillian College</p>
        </div>
        
        <div class="footer">
            <p>St. Maximillian College Admission System</p>
            <p>© {{ date('Y') }} St. Maximillian College. All rights reserved.</p>
            <p><small>If you did not apply to our college, please ignore this email or contact us immediately.</small></p>
            <p><small>📧 stmaximilliancollege@info.ac.tz | 📞 +255 623 053 846</small></p>
        </div>
    </div>
</body>
</html>