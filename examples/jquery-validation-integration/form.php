<!doctype html>
<html>
<head>
    <title>jQuery Validation Integration Example</title>
    <link type="text/css" href="form.css" rel="stylesheet" />
    <script type="text/javascript" src="jquery.js"></script>
    <script type="text/javascript" src="jquery.validate.js"></script>
    <script type="text/javascript" src="form.js"></script>
</head>
<body>
    <div>
        <form method="post" action="form-handler.php">
            <table>
                <tr>
                    <th><label for="first_name">First Name</label></th>
                    <td><input type="text" name="first_name" id="first_name" /></td>
                </tr>
                <tr>
                    <th><label for="last_name">Last Name</label></th>
                    <td><input type="text" name="last_name" id="last_name" /></td>
                </tr>
                <tr>
                    <th><label for="password_1">Password</label></th>
                    <td><input type="password" name="password_1" id="password_1" /></td>
                </tr>
                <tr>
                    <th><label for="password_2">Confirm Password</label></th>
                    <td><input type="password" name="password_2" id="password_2" /></td>
                </tr>
                <tr>
                    <th><label for="send_email_updates">Send Email Updates</label></th>
                    <td><input type="checkbox" name="send_email_updates" id="send_email_updates" /></td>
                </tr>
                <tr>
                    <th><label for="email_1">Email</label></th>
                    <td><input type="text" name="email_1" id="email_1" /></td>
                </tr>
                <tr>
                    <th><label for="email_2">Confirm Email</label></th>
                    <td><input type="text" name="email_2" id="email_2" /></td>
                </tr>
                <tr>
                    <th><label for="birth_date">Birth Date</label></th>
                    <td><input type="text" name="birth_date" id="birth_date" /></td>
                </tr>
                <tr>
                    <th><label for="url">Website URL</label></th>
                    <td><input type="text" name="url" id="url" /></td>
                </tr>
                <tr>
                    <th><label for="temperature">Temperature (in &deg;C)</label></th>
                    <td><input type="text" name="temperature" id="temperature" /></td>
                </tr>
                <tr>
                    <th><label for="speed">Speed (in m/s)</label></th>
                    <td><input type="text" name="speed" id="speed" /></td>
                </tr>
                <tr>
                    <th><label for="rating">Rating (between 1 and 100)</label></th>
                    <td><input type="text" name="rating" id="rating" /></td>
                </tr>
                <tr>
                    <th><label for="md5_hash">MD5 Hash</label></th>
                    <td><input type="text" name="md5_hash" id="md5_hash" /></td>
                </tr>
                <tr>
                    <th><label for="skip_client_validation">Skip Client Validation</label></th>
                    <td><input type="checkbox" name="skip_client_validation" id="skip_client_validation"/></td>
                </tr>
                <tr>
                    <th></th>
                    <td><input type="submit" /></td>
                </tr>
            </table>
        </form>
    </div>
</body>
</html>