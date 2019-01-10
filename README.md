# PHP client library for XMPP (Jabber) protocol

[![Latest Stable Version](https://poser.pugx.org/norgul/xmpp-php/v/stable)](https://packagist.org/packages/norgul/xmpp-php)
[![Total Downloads](https://poser.pugx.org/norgul/xmpp-php/downloads)](https://packagist.org/packages/norgul/xmpp-php)
[![Latest Unstable Version](https://poser.pugx.org/norgul/xmpp-php/v/unstable)](https://packagist.org/packages/norgul/xmpp-php)
[![License](https://poser.pugx.org/norgul/xmpp-php/license)](https://packagist.org/packages/norgul/xmpp-php)

**Disclaimer**: even though I called it a version, this is in no way production ready
and current repository state is really volatile due to my testing and all-around changes.

This is low level socket implementation for enabling PHP to 
communicate with XMPP due to lack of such libraries online (at least ones I 
could find). 

XMPP core documentation can be found [here](https://xmpp.org/rfcs/rfc6120.html).

Current version is oriented towards simplicity and XMPP understanding under the
hood. Should the need arise, I will expand the library, and by all means feel
free to contribute to the repository. 

# Installation requirements and example

Project requirements are given in `composer.json` file (this assumes you know what 
[Composer](https://getcomposer.org) is):

```
"require": {
    "php": ">=7.0",
    "psr/log": "^1.0"
},
```

You can use this library in your project by running:

```
composer require norgul/xmpp-php
```

You can see usage example in `Example.php` file by changing credentials to 
point to your XMPP server and from project root run `php Example.php`.

# Library usage
## Init
In order to start using the library you first need to instantiate a new `Options` 
class. Everything except setting a port number is required. If omitted, port 
will default to `5222` which is XMPP default. 

Username can be either bare `JID` or in `JID/resource` form. If you are using a bare `JID`
the resource will be added automatically. You can override this by explicitly setting a 
resource with `$client->setResource()`. In the second case the username will be automatically
parsed to `username` and `resource` variables. In case of `JID/resource/xyz` format, everything
after second slash will be ignored. If both `JID/resource` is present as well as using the
`$client->setResource()` method, second one will take precedence. 

```
$options = new Options();

$options
    ->setHost($host)            // required
    ->setPort($port)            // not required, defaults to 5222
    ->setUsername($username)    // required
    ->setPassword($password);   // required
```

`Options` object is required for establishing the connection and every other subsequent
request, so once set it should not be changed. 

Once this is set you can instantiate new `XmppClient` object and pass the `Options` object in.

## Connect & auth
```
$client = new XmppClient();
$client->connect($options);
```

`$client->connect()` method does a few things:
1. Connects to the socket which was initialized in `XmppClient` constructor
2. Opens XML stream to exchange with XMPP server
3. Tries to authenticate with the server based on provided credentials

Current version supports only `PLAIN` auth method. 

## Sending messages

`$client->sendMessage()` takes 3 parameters of which the last one is optional. First parameter
is the actual message you'd like to send, second one is recipient of the message and third
one is type of message to be sent. This is currently set to default to `CHAT`, but will probably
be extended in future releases

## Receiving messages and other responses

Server responses (or server side continuous XML session to be exact) can be retrieved with 
`$client->getResponse()`.

In case you are not interested in complete response which comes from server, you may also use 
`$client->getMessages()` which will match message tags with regex and return array of matched 
messages. In case you'd like to see the response in the terminal, you can do something like this:

```
do {
    $response = $client->getMessages();
    if($response)
        echo print_r($response);
} while (true);
```

`$client->getResponse()` actually takes an optional boolean parameter `echoOutput` which is a 
flag indicating whether the response should be echoed out. This is useful for testing from
terminal so when you set the flag to `true` you will be able to actually see the response
which will be returned. For all other purposes this flag should be set to `false`.

## Roster

`$client->getRoster()` takes no arguments and fetches current authenticated user roster. 

## Priority

`$client->setPriority()` sets priority for given resource. First argument is an integer 
`-128 <> 127`. If no second argument is given, priority will be set for currently used resource.
Other resource can be provided as a second argument whereas the priority will be set for that
specific resource. 

## More options (not required)

`Options` object can take more options which may be chained but are not required. These are explained
and commented in the code directly in the `Options` class:

```
$options
    ->setSocketWaitPeriod($wait)    // defaults to 1s
    ->setProtocol($protocol)        // defaults to TCP
    ->setResource($resource)        // defaults to 'norgul_machine' string + timestamp
    ->setAuthType(AuthTypeInterface $authType)  // defaults to Plain
```

# Other

`Example.php` has a `sendRawXML()` method which can be helpful with debugging. Method works in a way
that you can provide hand-written XML and send it to the server. On the other hand you can 
also trigger a method by providing method name instead of XML. 

```
Enter XML: <xml>foo</xml>           <-- will send XML
Enter XML: getRoster                <-- will run getRoster() method
Enter XML: requestPresence x@x.com  <-- will run with argument requestPresence(x@x.com)
```

Some valid XMPP XML will be declined (like sending `<presence/>`) because `simplexml_load_string()` 
is not able to parse it as being a valid XML. In cases you need to do some custom stuff like 
that and you are sure it is a XMPP valid XML, you can remove the parsing line and just let the
`send()` method do its magic.
 
 **Be aware! Be very aware!** sending an invalid XML to the server
will probably invalidate currently open XML session and you will probably need to restart the 
script. 
