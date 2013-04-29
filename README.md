#Mojavi#
======

Mojavi MVC Framework for PHP 5.  Mojavi was one of the first MVC frameworks made for PHP 5 using an object oriented design.  Though the original contributors have ceased development on it, many of it's concepts have made their way into current MVC frameworks, such as Symfony.  This version contains several ported files from other branches like Agavi and Symfony.  We've also finished some of the code stubs that were in the original version.

##Additions##
=============
We've added some additional components that do not exist in other Mojavi implementations.  These are listed below.

###util/StringTools###
String class used for general string manipulation.  Contains functions to colorize text in a Linux terminal.  Also functions to make strings safe for MySQL

###logging/LoggerManager###
Easy to use logging class built from the Java Log Manager.

    LoggerManager::error(__METHOD__ . " :: About to run some code...");
    LoggerManager::debug(__METHOD__ . " :: Here is my query: " . $ps->getPreparedStatement());

###forms/OrmForm###
Simple to use action forms.  An action form is a PHP class with getters and setters.  It is usually built from the database columns, but could have additional getters/setters to accomodate parameters passed in via an HTML Form.  It also can have helper functions with business logic.  

    $_GET['id'] = 1;
    $user_form = new UserForm();
    $user_form->populate($_GET);
    $user_form->query();
    if ($user_form->exists()) {
        echo $user_form->getUsername();
    }

###models/BasicModel###
Simple base class for models containing database queries.  Defines basic functions for a CRUD implementation such as performQuery(), performQueryAll(), performCount(), performDelete, performInsert(), and performUpdate()

    $user_form = new UserForm();
    $user_form->populate($_GET);
    $user_model = new UserModel();
    $user_model->performInsert($user_form);
    
###db/KeyBasedPreparedStatement###
Robust Prepared Statement class that doesn't use PDO.  You simply pass it a query with placeholders such as <<name>> and then call functions for it.
    
    function performQuery(Form $arg0) {
        /* @var $arg0 UserForm */
        $query = "SELECT * FROM user WHERE username = <<username>>";
        $ps = new KeyBasedPreparedStatement($query);
        $ps->setString("username", $arg0->getUsername());
    
        echo $ps->getPreparedStatement();
        // will return SELECT * FROM user WHERE username = 'tester'
    }

###util/IntegerEncoder###
Encodes ids as an 8 character id.  For instance, id=1 becomes id=z8e7f4d0.  Ids can be easily encoded and decoded on the fly.

###action/BasicConsoleController###
Console controller used for CLI scripts.  This controller will convert command line arguments to the $_GET array for easy population of forms and models.

###action/BasicRestController###
Rest controller used for an API project.  Extends the execute() function with executeGet, executePost, executePut, and executeDelete.  Allows for file uploads.  Populates a JsonForm for easy display and parsing.

##Installation##
You can include this project in yours by simply adding a folder called mojavi and adding it to your include_path.
