## Requirements

    - PHP => 7.0
    - Composer to be Installed Globally
    - Basic WordPress Understanding 
    
## Inpsyde Task Setup/Configuration


#### Virtual Host Setup
Create a virtual host locally and point the domain to the repository root where composer.json is located.
 


#### Update wp-config.php File
Update the Database and domain details. The domain variable in file should be updated to the current domain

```
    $domain = 'http://inpsyde.local';
```
  


#### Install the Project Dependencies 
Run the following command to install the project dependencies at project root where composer.json is located.

```
composer install
```



#### Plugin Activation
Login to the WordPress Admin and Activate the plugin `Inpsyde Task Plugin`


#### Done :)
Visit the site you will see the default WordPress setup with twenty twenty theme.
Plus there is a link in main menu `Inpsyde Task` will redirect you to the VueJS based template where our main project is implemented.
Or you can simply visit `http:yourdoamin.com/inpsyde`

## Coding Standards 
Inpsyde Coding standard's are followed in UnitTests and main Plugin Class. Please run the following command
 
```
Composer run codeSniff
```


## Unit Tests
UnitTesting is done via Brian Monkey Library. Please run the following command to run unit tests
 
```
Composer run tests
```
 
 
## Project Overview
The project install WordPress as the Part of composer dependency along with PHPUnit, Brain Monkey and Inpsyde Coding standard's.
The task is to send an API request to an external Server, get data from the server and then show the data in a custom WordPress EndPoint. 
So the plugin can be divided into three parts Back-End, Front-End and UnitTesting/Coding Standard's  


#### Back-End Part `(InpsydeTaskPlugin.php)`
The Plugin is implemented in a singleton class with Front-end in VueJS. The Plugin creates the Endpoint ``http://youdomain.com/inpsyde`` and ``http://youdomain.com/inpsyde/user/someInteger`` also load a new template for these endpoints located in the plugin directory.
The template is a complete Single Page VueJS application with its own routing system/rules that will get the data from WordPress Rest EndPoints
created by this plugin. The plugin also use transient to store data received from API. Rest Endpoints are responsible to provide the data for single user and all users.

Following are the Custom EndPoints For Users
````
- http://youdomain.com/inpsyde
- http://youdomain.com/inpsyde/user/4|someInteger
```` 

Following are the Rest EndPoints 
````
- http://youdomain.com/wp-json/inpsyde/v1/users
- http://yourdomain.com/wp-json/inpsyde/v1/users/4|someInterger 
```` 


### Front-End Part
The Front-End Part is implemented in VueJS and common BootStrap 4 template that will be loaded on custom endpoints for users mentioned above.
The Vue Application only sends one request to the WordPress Rest Endpoint `/wp-json/inpsyde/v1/users` in it's whole life cycle and receive all the users data.
We are using WebPack (JavaScript Module Bundler) to make the code compatible with browser that don't support ECMAScript 6 
and compile our front-end resources. There is no need to run any node command everything is already compiled.

Following are the features
 
 - Single Page application on VueJs
 - Vue Router library is implemented 
 - Datatables library is implemented on users table
 - BootStrap 4 is used as css framework
 - WebPack as module bundler



### UnitTests and Coding standards
UnitTesting is done with Brain Monkey and our main Class is `InpsydePluginTest`. I have covered the limited part of the code mainly the cached API response part.
All the Tests are located inside tests directory and tests can be run from the command mentioned [above.](#unit-tests)


**InpsydeCoding Standards**  
Coding standards are completely followed. Please use the command [above.](#coding-standards) to run the Check.  


