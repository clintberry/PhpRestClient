Rest Client for PHP 5.3+
========================

Overview
--------

This is a Rest Client class for PHP 5.3+. It allows easy function calls to remote REST servers. 

_Requires CURL_

Example Usage
-------------

Initialize the class and set the REST api url and the data type

```php
use CB\PhpRest\Client

//Still working on oath part
$myClient = new Client('https://api.twitter.com/1', 'json');
//Get the timeline
$timeline = $myClient->get('/statuses/home_timeline.json');
```

It also supports put(), post(), and delete()

```php
$twilioClient = new Client('https://api.twilio.com/2010-04-01', 'json');
    
//Make a call on twilio
$icallData = array('From'=>'18015551234', 'To'=>'801-555-4321' , 'Url'=>'http://www.myapp.com/myhandler.php');
$twilioClient->post('/Accounts/{account_number}/Calls', $callData);
```

License
-------

Released under the [MIT license](http://creativecommons.org/licenses/MIT/).
