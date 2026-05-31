<?php  
session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <title>Registration Form</title>

    <!-- Main CSS -->
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/login.css">

    <style>
        /* Custom styles for the registration form */
        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url('../uploads/FKom.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(12px);
            transform: scale(1.05);
            z-index: -2;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.28);
            z-index: -1;
        }

        .container_form {
            text-align: center;
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.85);
            padding: 32px;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.35);
            width: 440px;
            border: 1px solid rgba(255, 255, 255, 0.65);
        }

        .input-box {
            margin-bottom: 15px;
            position: relative;
        }

        .input-box input[type="text"],
        .input-box input[type="email"],
        .input-box input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .input-box i{
            position: absolute;
            right: 8px;
            top: 30%;
            transform: translate(-50%);
            color: #050101;
        }
        
        .radio-group {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
            padding: 8px 0;
        }

        .radio-group label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            color: #333;
            cursor: pointer;
            margin: 0;
        }

        .radio-group input[type="radio"] {
            width: auto;
            margin: 0;
            cursor: pointer;
        }

        .register-btn,
        .reset-btn {
            width: 48%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .register-btn {
            background-color: #28a745;
            color: #fff;
        }

        .reset-btn {
            background-color: #dc3545;
            color: #fff;
        }
        </style>
</head>
<body>

    <div class="container_form">
        <form action="" method="post">
            <h3><b>Registration Form</b></h3>

            <br>

            <div class="input-box">
                <input type="text" placeholder="Full Name" name="full_name" required>
                <i class='bx bx-user'></i>
            </div>

            <div class="input-box">
                <input type="text" placeholder="Matric Number" name="matric_number" required>
                <i class='bx bx-id-card'></i>
            </div>

            <div class="input-box">
                <input type="text" placeholder="IC Number" name="ic_number" required>
                <i class='bx bx-id-card'></i>
            </div>

            <div class="input-box">
                <input type="email" placeholder="Email" name="email" required>
                <i class='bx bx-envelope'></i>
            </div>

            <div class="input-box">
                <input type="text" placeholder="Phone Number" name="phone_number" required>
                <i class='bx bx-phone'></i>
            </div>

            <div class="input-box radio-group">
                <label for="male">
                    <input type="radio" name="gender" value="male" id="male" required>
                    Male
                </label>
                <label for="female">
                    <input type="radio" name="gender" value="female" id="female" required>
                    Female
                </label>
            </div>

            <button type="submit" name="register" class="register-btn">Submit</button>
            <button type="reset" class="reset-btn">Reset</button>
        </form>
    </div>
</body>
</html>