<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentDegree;
use App\Models\ContactInfo;
use App\Models\EmploymentInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Responses\ApiResponse;
use App\Events\Registered;
use Illuminate\Support\Str;
use App\Mail\VerifyEmail;
use App\Mail\RegistrationPendingApproval;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // Registration endpoint
    public function register(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            // Personal Info
            'title' => 'nullable|string|max:10',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'otherNames' => 'nullable|string|max:255',
            'maidenName' => 'nullable|string|max:255',
            'dob' => 'required|date|before:today',
            'nationality' => 'required|string|max:255',
            'countryOfResidence' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'gender' => 'nullable|string|in:male,female,other',

            // Contact Info
            'email' => 'required|string|email|max:255|unique:users',
            'phoneNumber' => 'required|string|max:20|unique:users,phone_number',
            'otherEmailAddress' => 'nullable|email|max:255',
            'otherTelephoneNumber' => 'nullable|string|max:20',

            // Academic History
            'hallOfResidence' => 'nullable|string|max:255',
            'degrees' => 'nullable|array',
            'degrees.*.degreeReceived' => 'required_with:degrees|string|max:255',
            'degrees.*.degreeLevel' => 'nullable|in:certificate,diploma,bachelor,master,phd,other',
            'degrees.*.college' => 'nullable|string|max:255',
            'degrees.*.department' => 'nullable|string|max:255',
            'degrees.*.yearOfCompletion' => 'required_with:degrees|integer|min:1950|max:' . (date('Y') + 10),
            'degrees.*.classification' => 'nullable|string|max:255',
            'degrees.*.isPrimary' => 'nullable|boolean',

            // Employment Info
            'employerName' => 'nullable|string|max:255',
            'jobTitle' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'employmentCountry' => 'nullable|string|max:255',
            'employmentStartDate' => 'nullable|date',  // first employment start date
            'currentEmploymentStartDate' => 'nullable|date',
            'linkedinProfile' => 'nullable|url|max:500',
            'personalWebsite' => 'nullable|url|max:500',

            // Preferences
            'shareWithAlumniAssociations' => 'nullable|boolean',
            'includeInBirthdayList' => 'nullable|boolean',
            'receiveNewsletter' => 'nullable|boolean',

            // Chapter assignment
            'chapter_id' => 'nullable|exists:chapters,id',

            // Password - now optional
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        // Begin database transaction
        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'title' => $request->title,
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'other_names' => $request->otherNames,
                'maiden_name' => $request->maidenName,
                'dob' => $request->dob,
                'nationality' => $request->nationality,
                'country_of_residence' => $request->countryOfResidence,
                'bio' => $request->bio,
                'gender' => $request->gender,
                'phone_number' => $request->phoneNumber,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : null,
                'hall_of_residence' => $request->hallOfResidence,
                'linkedin_profile' => $request->linkedinProfile,
                'personal_website' => $request->personalWebsite,
                'share_with_alumni_associations' => $request->shareWithAlumniAssociations ?? false,
                'include_in_birthday_list' => $request->includeInBirthdayList ?? false,
                'receive_newsletter' => $request->receiveNewsletter ?? true,
                'is_verified' => true,
                'is_active' => false,
                'is_approved' => false,
                'email_verification_token' => null,
                'first_employment_start_date' => $request->currentEmploymentStartDate
            ]);

            // Create contact info (if you're using separate table)
            if ($request->otherEmailAddress || $request->otherTelephoneNumber) {
                ContactInfo::create([
                    'user_id' => $user->id,
                    'email_address' => $request->email,
                    'telephone_number' => $request->phoneNumber,
                    'other_email_address' => $request->otherEmailAddress,
                    'other_telephone_number' => $request->otherTelephoneNumber,
                ]);
            }

            // Create student degrees
            if ($request->has('degrees') && is_array($request->degrees)) {
                foreach ($request->degrees as $degree) {
                    StudentDegree::create([
                        'user_id' => $user->id,
                        'degree_received' => $degree['degreeReceived'],
                        'degree_level' => $degree['degreeLevel'] ?? null,
                        'college' => $degree['college'] ?? null,
                        'department' => $degree['department'] ?? null,
                        'year_of_completion' => $degree['yearOfCompletion'],
                        'classification' => $degree['classification'] ?? null,
                        'is_primary' => $degree['isPrimary'] ?? false,
                        'institution' => 'University of Ghana',
                    ]);
                }
            }

            // Create employment info (if provided)
            if ($request->employerName || $request->jobTitle) {
                EmploymentInfo::create([
                    'user_id' => $user->id,
                    'employer_name' => $request->employerName,
                    'job_title' => $request->jobTitle,
                    'industry' => $request->industry,
                    'country' => $request->employmentCountry,
                    'employment_start_date' => $request->currentEmploymentStartDate,
                    'is_active' => true,
                ]);
            }

            // Assign user to chapter if provided
            if ($request->chapter_id) {
                $user->assignToChapter($request->chapter_id, true);
            }

            // Send pending approval email
            Mail::to($user->email)->send(new RegistrationPendingApproval($user));

            // Fire Registered event
//            event(new Registered($user));

            // Commit transaction
            DB::commit();

            // Create Passport token
//            $token = $user->createToken('Alumni API Token')->accessToken;

            return ApiResponse::success([
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'is_approved' => $user->is_approved,
                ],
//                'token' => $token,
                'message' => 'Registration successful. Please check your email. Your account is pending approval from the university.',
            ], 'Registration successful', 201);

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            // Log the error
            \Log::error('Registration error: ' . $e->getMessage());

            return ApiResponse::error('Registration failed. Please try again.', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // Resend verification email
    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error', 422, $validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if ($user->is_verified) {
            return ApiResponse::error('Email already verified', 400);
        }

        // Generate new verification token
        $user->email_verification_token = Str::random(64);
        $user->save();

        // Send verification email
        Mail::to($user->email)->send(new VerifyEmail($user));

        return ApiResponse::success([], 'Verification email sent successfully');
    }


// Verify email
    public function verifyEmail(Request $request, $token, $email)
    {
        $user = User::where('email', $email)->first();

        // Check if user is already verified
        if ($user->is_verified && $user->email_verified_at) {
            return ApiResponse::success([
                'user' => $user,
                'already_verified' => true
            ], 'Email already verified', 200);
        }

        $user = User::where('email_verification_token', $token)->first();



        // If no user found with token, check if token might be null (already verified)
        if (!$user) {
            // Try to find user by looking at verified users (token would be null after verification)
            // This won't work with current logic, so return error
            return ApiResponse::error('Invalid verification token', 400);
        }



        // Verify the user
        $user->is_verified = true;
        $user->is_active = true;
        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        return ApiResponse::success([
            'user' => $user,
            'already_verified' => false
        ], 'Email verified successfully', 200);
    }

    // Login endpoint
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error', 422, $validator->errors());
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        $user = Auth::user();

        // Check if account is approved
        if (!$user->is_approved) {
            Auth::logout();
            return ApiResponse::error('Your account is pending approval from the university', 403);
        }

        // Check if account is active
        if (!$user->is_active) {
            Auth::logout();
            return ApiResponse::error('Your account is not active. Please contact support.', 403);
        }

        // Check if email is verified
        if (!$user->is_verified) {
            Auth::logout();
            return ApiResponse::error('Please verify your email before logging in', 403);
        }

        $user->contactInfo;

        $user->degrees;

        $user->currentEmployment;


        // Create API token
        $token = $user->createToken('API Token', ['alumni'])->accessToken;

        return ApiResponse::success([
            'user' => $user,
            'token' => $token,
        ], 'Login successful');
    }

    // Logout endpoint
    public function logout(Request $request)
    {
        Auth::logout();
        return ApiResponse::success([], 'Logged out successfully');
    }
}
