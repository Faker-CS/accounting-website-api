<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome to our family, MoneyTeers happy to be part of it !!!</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f9; font-family: Arial, sans-serif;">

  <div style="max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #e0e0e0;">
    
    <!-- Header -->
    <div style="background: linear-gradient(120deg, #007bff, #3399ff); color: white; text-align: center; padding: 30px 20px;">
      <h1 style="margin: 0; font-size: 26px;">Welcome to Our Service</h1>
    </div>

    <!-- Content -->
    <div style="padding: 30px 20px; color: #333333; line-height: 1.7;">
      <p style="margin-top: 0;">Dear <span style="font-weight: bold; color: #007bff;">{{ $data['name'] }}</span>,</p>
      <p>Your account has been successfully created with the following credentials:</p>
      <ul style="padding-left: 20px; margin: 10px 0;">
        <li>Email: <strong>{{ $data['email'] }}</strong></li>
        <li>Password: <strong>{{ $data['password'] }}</strong></li>
      </ul>
      <p>You can now log in and start using our services.</p>

      <!-- 3D Button -->
      <a href="#" style="display: inline-block; padding: 12px 24px; margin-top: 20px; background: linear-gradient(145deg, #0069d9, #3399ff); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; box-shadow: 0 4px 8px rgba(0,0,0,0.2); transition: transform 0.2s ease, box-shadow 0.2s ease;">
        Log In
      </a>
    </div>

    <!-- Footer -->
    <div style="text-align: center; padding: 16px; background-color: #f4f4f9; font-size: 12px; color: #999999;">
      <p style="margin: 0;">&copy; {{ date('Y') }} Our Company. All rights reserved.</p>
    </div>

  </div>

</body>
</html>
