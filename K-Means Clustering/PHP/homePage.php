<?php
/**
 * Contains the html information for the home page of the web application.
 */
echo <<< _FINISH
    <html>
        <head>
            <title>K-Means Based Clustering Web Application</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="homePage.css"  type="text/css" rel="stylesheet" />
            <link rel="stylesheet" href="https://use.typekit.net/nar5czb.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

            <!-- activate link script-->
            <script>
                $(document).ready(function()
                {
                    var navLinks = $(".nav .navlinks a");
                    navLinks.click(function()
                    {
                        var selectedLink = $(this);
                        activateLink(selectedLink);
                    })

                    function activateLink(selectedLink)
                    {
                        $(".nav .navlinks a.active").removeClass("active");
                        selectedLink.addClass("active");
                    }
                });
            </script>
        </head>
        <body>
            <!-- main container-->
            <div class="container">

                <header>
                    <a id="top"></a>
                    <div class="nav">

                        <div class="left-side-title">
                            <h1>CS 174</h1>
                        </div>
                        

                        <div class="navlinks">
                            <ul>
                                <li><a href="" class="active">Home</a></li>
                                <li><a href="loginPage.php">Login</a></li>
                                <li><a href="registerPage.php">Register</a></li>
                            </ul>
                        </div>


                        <div class="right-side-title">
                            <h1>Brian Tamsing</h1>
                        </div>
                    </div> <!-- end of the "navbar titles" container-->
                </header>

                <!-- home or landing page section-->
                <section id="landing-page">
                    <div class="container">
                        <div class="landing-title">
                            <h1>K-Means Based Clustering Web Application</h1>
                        </div> 
                        <div class="description">
                                <p>
                                    Welcome to my web application! This is a web application which implements a k-means based, unsupervised 
                                    learning, ML algorithm for a single, one-dimensional dataset.
                                    <p class="indented">
                                        -Simply register for an account, sign in, and get started! It's that easy!
                                    </p>
                                    <p id="note">
                                        *This web application was built and designed for my CS 174 class at San Jose State University, utilizing the following technologies: <span>HTML5, CSS3/SCSS, JavaScript, jQuery, AJAX, PHP, and MySQL
                                        </span>.
                                    </p>
                                </p>
                        </div>
                    </div> <!-- end of the "main-content" container-->
                </section>
                <!-- end of home or landing page section-->

            </div> 
            <!-- end of the "main" container-->
        </body>
    </html>
_FINISH;

?>