<html>

    <head>
        <title>ZipCompare Login Portal</title>
        <link rel="stylesheet" type="text/css" href="../../css-pure/pure-min.css">
        <link rel="stylesheet" type="text/css" href="css/index.css"> </head>

    <body>
            
        <div id="log-form">
            <h1 id="log-title">
                ZipCompare Login
            </h1>
            <form class='pure-form pure-form-aligned' <?php $self=htmlspecialchars($_SERVER[ "PHP_SELF"]); echo ( "action='$self'"); ?> method='POST'>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username </label>
                        <input id="username" type="username" placeholder="name123" name="username" autocapitalize="off" autocorrect="off" required> </div>
                    <div class="pure-control-group">
                        <label for="password">Password </label>
                        <input type="password" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])\w{6,}$" autocapitalize="off" autocorrect="off" placeholder="Password" id="password" name="password" required>                     
                    </div>
                    <div class="pure-controls">
                        <button type="submit" class="pure-button pure-u-24-24 pure-button-primary">Login</button>
                    </div>
                </fieldset>
            </form>
            <div id="register">
                <br> Create an Account &nbsp;
                <a href="index.php">
                    <button class="pure-button" type="button">Register</button>
                </a>
            </div>
            <br>
            <div id="forgot-pass">
                <a href="../forms/editAdvisor.php">
                    Change your password?
                </a>
            </div>
        </div>
    </body>

    </html>