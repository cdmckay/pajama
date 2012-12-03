<!doctype html>
<html>
<head>
    <title>Simple Example</title>
    <link type="text/css" href="form.css" rel="stylesheet" />
</head>
<body>
    <div>
        <?php if (isset($_GET['successful'])): ?>
        <div id="form-errors">
            <?php echo 'Form submission ' . ($_GET['successful'] ? ' was successful' : ' has failed') . '.'; ?>
        </div>
        <?php endif; ?>
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
                    <th></th>
                    <td><input type="submit" /></td>
                </tr>
            </table>
        </form>
    </div>
</body>
</html>