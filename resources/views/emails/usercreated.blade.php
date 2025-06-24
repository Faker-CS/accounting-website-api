<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome to MoneyTeers – Happy to Have You!</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f7fafc; font-family: 'Arial', sans-serif; line-height: 1.6;">

  <!-- Main Container -->
  <div style="max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08); overflow: hidden; border: 1px solid #e2e8f0;">

    <!-- Header with Gradient -->
    <div style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-align: center; padding: 40px 20px;">
      <img src="c:\Users\ASUS\OneDrive\Desktop\pfe-faker\starter-vite-js\public\logo\logo-single.png" alt="MoneyTeers Logo" style="max-width: 180px; margin-bottom: 20px;">
      <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Welcome to the Family!</h1>
      <p style="margin: 10px 0 0; opacity: 0.9; font-size: 16px;">We're thrilled to have you onboard.</p>
    </div>

    <!-- Content Section -->
    <div style="padding: 32px 24px; color: #2d3748;">
      <p style="margin-top: 0; font-size: 16px;">Dear <span style="font-weight: 600; color: #4f46e5;">{{ $data['name'] }}</span>,</p>
      <p style="margin-bottom: 20px;">Thank you for joining MoneyTeers! Your account has been successfully created. Below are your login credentials:</p>

      <!-- Credentials Box -->
      <div style="background: #f8fafc; border-left: 4px solid #4f46e5; padding: 16px; margin: 20px 0; border-radius: 0 6px 6px 0;">
        <p style="margin: 0 0 8px; font-weight: 600; color: #4f46e5;">Your Account Details:</p>
        <ul style="margin: 0; padding-left: 20px;">
          <li style="margin-bottom: 8px;"><strong>Email:</strong> {{ $data['email'] }}</li>
          <li><strong>Password:</strong> {{ $data['password'] }}</li>
        </ul>
      </div>

      <p style="margin-bottom: 24px;">For security reasons, we recommend changing your password after your first login.</p>

      <!-- CTA Button -->
      <div style="text-align: center; margin: 28px 0;">
        <a href="#" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); transition: all 0.3s ease;">
          Log In to Your Account
        </a>
      </div>

      <p style="margin-bottom: 0; font-size: 15px;">If you have any questions, feel free to contact our support team at <a href="mailto:support@moneyteers.com" style="color: #4f46e5; text-decoration: none;">support@moneyteers.com</a>.</p>
    </div>

    <!-- Footer -->
    <div style="text-align: center; padding: 20px; background-color: #f7fafc; color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0;">
      <p style="margin: 0;">© {{ date('Y') }} <strong>MoneyTeers</strong>. All rights reserved.</p>
      <p style="margin: 8px 0 0; font-size: 12px; opacity: 0.8;">Designed with ❤️ for our community</p>
    </div>
  </div>
</body>
</html>