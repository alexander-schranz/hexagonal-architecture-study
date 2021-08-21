# Project Rabbit Hole

> Dig into the rabbit hole of Hexagonal Architecture.

## Table of Contents

 - [1. Introducing](#1-introducing)
 - [2. Bounding Context](#2-bounding-context)
 - [3. Hexagonal Architecture](#3-hexagonal-architecture)
   - [3.1 Domain Layer](#31-domain-layer)
   - [3.2 Application Layer](#32-application-layer)
   - [3.3 Infrastructure Layer](#33-infrastructure-layer)
   - [3.4 UserInterface Layer](#34-userinterface-layer)
 - [4. Project Targets](#4-project-targets)
 - [5. Creating Domain Layer](#5-creating-domain-layer)
   - [5.1 Modeling Domain Models](#51-modeling-domain-models)
   - [5.2 Defining Domain Services](#52-defining-domain-services)
 - [6. Creating Application Layer](#6-creating-application-layer)
   - [6.1 Creating Messages](#61-creating-messages)
   - [6.2 Validating Messages](#62-validating-messages)
   - [6.3 Creating Message Handlers](#63-creating-message-handlers)
   - [6.4 Make Handlers extendable](#64-make-handlers-extendable)
   - [6.5 Third party code inside application](#65-third-party-code-inside-applicatin)
 - [7 Testing Application Core](#7-testing-application-core)
   - [7.1 Mocks vs no Mocks](#71-mocks-vs-no-mocks)
   - [7.2 AAA Pattern](#72-aaa-pattern)
 - [8. Creating Infrastructure Layer](#8-creating-infrastructure-layer)
   - [8.2 Creating Doctrine ORM Integration](#82-creating-doctrine-orm-integration)
   - [8.3 Creating Cycle ORM Integration](#83-creating-cycle-orm-integration)
   - [8.4 ORM Datamapper vs Active Record](#84-orm-datamapper-vs-active-record)
   - [8.5 Creating Symfony Integration](#85-creating-symfony-integration)
   - [8.6 Creating Spiral Integration](#86-creating-spiral-integration)
 - [9 Testing Infrastructure Layer](#9-testing-infrastructure-layer)
   - [9.1 Create reusable test cases](#91-create-reusable-test-cases)
   - [9.2 Create reusable test traits](#92-create-reusable-test-traits)
 - [10. Creating UserInterface Layer](#10-creating-userinterface-layer)
   - [10.1 Create PSR 7 Controller](#101-create-psr-7-controller)
 - [11. Communicate between Bounded Contexts](#11-communicate-between-bounded-contexts)
   - [11.1 Process Manager](#111-process-manager)
 - [12. Porting Application Core into another language](#12-porting-application-core-into-another-language)
 - [00. Summary](#00-summary)
 - [00.1 Thank you](#001-thank-you)
 - [00.2 Links](#002-links)

## 1. Introducing

The "Project Rabbit Hole" is a case study by me - Alexander Schranz. I'm 
currently working as Web Developer for the Open Source Content Management 
System called [Sulu](https://github.com/sulu) and did begin my career as 
developer in 2012. Today I'm mostly working with
[Symfony](https://github.com/symfony/symfony),
[Doctrime ORM](https://github.com/doctrine/orm),
[Elasticsearch](https://github.com/elastic/elasticsearch),
[Redis](https://github.com/redis/redis) and [ReactJS](https://reactjs.org).

At [Sulu](https://sulu.io) we did more and more use in our projects the 
Hexagonal architecture. This case study should show the advantages of using 
the Hexagonal architecture. How I'm interpreting it and how it does solve 
for me the problem of creating long term maintainable software.

The case study focus on creating a reusable library which can be used in 
different frameworks without the need of changing its core application logic.
It should also show where the weak points are and why some frameworks suit 
better to create sustainable software and why others not.

## 2. Bounding Context

Before dig into the Hexagonal architecture I want to do a detour to Domain
Driven Design (DDD). In there exist the term of Bounding Contexts which is a 
way to help to split your application into different contexts which should 
work by their own.

The best example of how a software can be split into multiple context is a 
shop. A typical shop software can be split into the following context:

 - Product
 - Order
 - Invoice

Every context can work by itself. Example your website does only need to 
present products on pages. In this case there is no order or invoice needed.
So the product is defined as its own bounded context. In other case the 
order context can exist without invoice because invoicing is done over a 
third party service.

In this Contexts the Order does just provide an API / Interfaces to create 
Order Items, but does know nothing about the Product. In this type of 
context setup it's also easier to add additional thing which can be ordered, in 
example an own Ticket context.

The contexts a shop would then look like this:

```
+---------+
| Product |-------+      
+---------+       |       +-------+       +---------+
                  +-------+ Order |-------+ Invoice |
+---------+       |       +-------|       +---------+
| Ticket  |-------+
+---------+
```

We will go deeper into how the contexts can communicate / integrate in the 
chapters about the Hexagonal architecture.

The hardest thing in the initial project setup is defining the contexts, 
what does belong into which context. Having to many contexts can make the 
software hard to maintain and make it hard to understand how things work 
together. Having to less can make the code also messy and rewrites of 
specific contexts hard or even impossible in certain time.

About bounded context and DDD have also a look at Martin Fowlers website: 
[https://martinfowler.com/tags/domain%20driven%20design.html](https://martinfowler.com/tags/domain%20driven%20design.html)
about DDD topics.

If creating a library mostly your library is the whole context. In the example
code the context is called Event and will implement a simple translatable
Event model which can be created, modified, loaded and removed.

## 3. Hexagonal architecture

The ["Hexagonal architecture"](https://en.wikipedia.org/wiki/Hexagonal_architecture_(software))
also called "Ports and Adapters", is a way of how you can split your 
context/library into a maintainable and reusable way.

If you are interested in the origin of the hexagonal architecture you should
definitely have a look at the Alistair Cockburn first published article
about it on his website. Sadly currently only available in the webarchive
[https://web.archive.org/web/20170730135337/http://alistair.cockburn.us/Hexagonal+architecture](https://web.archive.org/web/20170730135337/http://alistair.cockburn.us/Hexagonal+architecture).

Most which I'm familiar with is coming from the great Matthias Noback which
did write several articles about this topic which can be found on his website
[https://matthiasnoback.nl/tags/hexagonal%20architecture/](https://matthiasnoback.nl/tags/hexagonal%20architecture/).

The architecture is mostly shown the following way:

```
+--------------------------------------------------------+
|                                                        |
|                         Adapters                       |
|                                                        |
|          +----------------------------------+          |
|          |                                  |          |
|          |               Ports              |          |
|          |                                  |          |
|          |       +------------------+       |          |
|          |       |                  |       |          |
| Adapters | Ports | Application Core | Ports | Adapters |
|          |       |                  |       |          |
|          |       +------------------+       |          |
|          |                                  |          |
|          |               Ports              |          |
|          |                                  |          |
|          +----------------------------------+          |
|                                                        |
|                         Adapters                       |
|                                                        |
+--------------------------------------------------------+
```

Here we already see why it is called "Ports & Adapters" the Application core 
communicates over ports with its adapters.

We will use a common Layered Hexagonal Architecture in our code. The main 
business logic should in this architecture exist in the `Application Core`,
which we will split into the `Domain` and the `Application` namespace. The 
adapters we will split into `UserInterface` and `Infrastructure`. This layers 
are also common without the UserInterface so the UserInterface adapters there 
live in the Infrastructure layer. The ports to the adapters should be kept that
the adapters are easily replaceable or new adapters can be added:

```
|       Adapters       | Port |                  Application Core               | Port |           Adapters           |
+----------------------+------+-------------------------------------------------+------+------------------------------+
|                      |      |                                                 |      |                              |


                                                           +--------------------+
                                                           |       Domain       |
                    +------------------------------------->|                    |      +-----------------------------+
                    |                                      |   +------------+   |      |        Infrastructure       |
 +---------------------+      +------------------------+   |   |   Model    |   |      |                             |
 |    UserInterface    |      |      Application       |-->|   +------------+   |      |   +---------------------+   |
 |                     |      |                        |   |                    |<-----|   | Doctrine Repository |   |
 |   +------------+    |      |   +----------------+   |   |   +------------+   |      |   +---------------------+   |
 |   | Controller |    |----->|   |     Message    |   |-->|   | Repository |   |      |                             |
 |   +------------+    |      |   +----------------+   |   |   +------------+   |      |   +---------------------+   |
 |                     |      |                        |   |                    |<-----|   |     Serializer      |   |
 |   +------------+    |      |   +----------------+   |   |   +------------+   |      |   +---------------------+   |
 |   |   Command  |    |----->|   | MessageHandler |   |-->|   | Exception  |   |      |                             |
 |   +------------+    |      |   +----------------+   |   |   +------------+   |      |   +---------------------+   |
 +---------------------+      +------------------------+   |                    |<-----|   |     Message Bus     |   |
           |                              A                |   +------------+   |      |   +---------------------+   |
           |                              |                |   |    Event   |   |      +-----------------------------+
           |                              |                |   +------------+   |             |        A
           |                              |                +--------------------+             |        |
           |                              |                                                   |        |
           |                              +---------------------------------------------------+        |
           |                                                                                           |
           +-------------------------------------------------------------------------------------------+
```

The Application Core, which includes the `Domain` and `Application` namespaces,
should have no external dependency when adopting a strict Hexagonal
Architecture.

The Adapters, which live in the `UserInterface` and `Infrastructure` namespaces,
which are very specific like the Repository implemented with 
an ORM like Doctrine lives in the Infrastructure namespace and are 
referenced in the Application Core just by a RepositoryInterface and by the 
`Infrastructure` Code for Symfony Framework correctly injected over 
Dependency Injection.

So now we already did talk about the 4 Layers of our Hexagonal Architecture 
which are:

 - Application
 - Domain
 - Infrastructure
 - UserInterface

The Hexagonal Architecture defines a specific way which layers are allowed 
to access the models and services of other layers. This is shown in the 
previous graphic with the arrows.

### 3.1. Domain Layer

The domain layer is the layer which contains your domain logic and is only 
allowed to access classes and interface of itself.
It provides mostly the following:

 - `Events`
 - `Exceptions`
 - `Factories`
 - `Models`
 - `Repository`

The models should be defined as rich models, so they should contain the logic 
for your domains, thrown expected exception for the Application or 
UserInterface. As Port to other Layers the Domain layer should provide for 
example an Interface for the Persistence Layer over a 
RepositoryInterface. The Interface can be used then by an Infrastructure 
Implementation. This type of pattern is well known as the "Dependency 
Inversion Principle" (DIP).

### 3.2. Application Layer

The application layer defines the services and application models. It is 
allowed to access the [Domain Layer](#31-domain-layer) and itself. In our case
mostly the following exist in the Application Layer:

 - `Messages`
 - `MessageHandlers`
 - any other application service provided by your library/project

Actually the Messages and MessageHandlers are in our Setup Commands and 
CommandHandlers from CommandBus. But as command are in most framework 
associated with CLI scripts we did decide to go here with Message and 
MessageHandler how it is done in the Symfony Framework.

In real-world applications you will find yourself quickly in
a place of reinventing the wheel. That’s the reason why we allow the usage of
some external dependency like PSR EventDispatcher or MessageBusInterface in the
Application namespace and Ramsey Uuid in our Domain Models. See also 
[Project Targets](#4-project-targets).

### 3.3. Infrastructure Layer

The infrastructure layer defines your adapters to other libraries like ORMs 
or to integration of your code into frameworks. The infrastructure is 
allowed to access the [Domain Layer](#31-domain-layer) and the
[Application Layer](#32-application-layer). In our case mostly the following
exist in the Application Layer:

 - `Framework` Integrations
   - Dependency Injection
 - `ORM` Integrations
   - Repository

As we need to keep the Domain and Application free from Infrastructure code 
things like ORM annotations should be replaced with xml files or defining 
the ORM schema over external code files.

The Hexagonal Architecture targets that such things like an ORM can in future
be easy replaceable. As an example replacing Doctrine with Cycle ORM should
have no impact on your Business Logic and should so easily be achievable with
this architecture. Or another example the service are registered in Symfony 
correctly providing in the Symfony Infrastructure Namespace a Bundle class 
or for the Spiral framework providing a Bootloader class.

### 3.4. UserInterface Layer

The userinterface layer as it says is where user input is coming in. The 
userinterface is allowed to access all other layers so the 
[Domain Layer](#31-domain-layer), [Application Layer](#32-application-layer)
and [Infrastructure Layer](#33-infrastructure-layer). The UserInterface 
layer contains in our setup:

 - Controllers
 - Commands (CLI Scripts)

Different frameworks need that there controllers or commands are written in 
a certain way. See also [Project Targets](#4-project-targets).

## 4. Project Targets

The first target of the Project "Rabbit Hole" is to give other developers a
great overview about Hexagonal architecture. This should not only be done by 
this text but also by a whole implemented library which code the reader can
analyse. So the developers are not getting lost in the "Rabbit Hole" of
Hexagonal architecture.

The second target and personally target is finding a balance between a strict
Hexagonal architecture and avoiding reinventing the wheel. This should be 
done by build on top of widely spread interfaces providing the 
[PSR packages](https://www.php-fig.org/psr/) and other well spread library. 
As example here [ramsey/uuid](https://github.com/ramsey/uuid) and
[flysystem](https://github.com/thephpleague/flysystem). I think this balance 
is very important to keep the development productive and that the 
architecture is not a stopper and so drawing the line must be defined for each
individual service.

The third target of this implementation is to show which frameworks make it 
harder to implement its infrastructure / userinterface layers and requires more 
work and which can easily. Why some patterns don't work and why others are 
better suited for such libraries.

The fourth target is can user interfaces defined which can be used by 
different frameworks, so does a framework change maybe also don't need to 
require that your libraries user interfaces also don't need to be rewritten. 
And so a library can easily support multiple frameworks and provide so its 
model and entities for it.

The project is for maintainable creating in a monorepository, the library 
will exists in the [src/event](src/event) directory with its Hexagonal 
Architecture. 
The proof that the library can be used in different framework is done by 
installing different framework skeletons into the [frameworks](frameworks) 
directory which will over local composer package install the library and use
its framework integration.

After this introduction in the theoretical part it is time to go into the 
practical part.

## 5. Creating Domain Layer

The domain namespace is responsible for the domain models and services. 
Common things inside it are Models, Exceptions, Events, Repository (Port), 
Factories.

### 5.1. Modeling Domain Models

The best way of defining the Domain Models is to begin with some kind of class 
diagram. This allows you to discuss relations between your models without 
the need of actually implementing them. In our example we have a very basic 
models with an `Event` and an `EventTranslation`. Modeling the Domain classes 
should help you also define the Bounded Contexts which from
[Chapter 2 - Bounding Context](#2-bounding-context).

```
+-----------------------------------[ Event Context ]---------------------------------+
|                                                                                     |
|     +---------------------------------+               +-----------------------+     |
|     |           Event                 |               |  EventTranslation     |     |
|     +---------------------------------+               +-----------------------+     |
|     | -id: int                        | 1           n | -id: int              |     | 
|     | -defaultLocale: string          |<------------->| -locale: string       |     |
|     +---------------------------------+               | -title: string        |     |
|     | + getDefaultLocale(): string    |               +-----------------------+     |
|     | + getTranslations(): i<ET>      |               | + getTitle(): string  |     |
|     | ...                             |               | ...                   |     |
|     +---------------------------------+               +-----------------------+     |
|                                                                                     |
+-------------------------------------------------------------------------------------+
```

In which detail you creating this diagrams is always up to you. Now as we 
have defined our Models we can begin to implement them.

 - [Event.php](./src/event/src/Domain/Model/Event.php)
 - [EventTranslation.php](./src/event/src/Domain/Model/EventTranslation.php)

After this we extract the Model public methods into an Interface mostly IDEs 
are helping here like Intellij/PHPStorm from Jetbrains. After that we should 
use everywhere where we are expecting a specific Model using the Interface 
instead of the Model class.

 - [EventInterface.php](./src/event/src/Domain/Model/EventInterface.php)
 - [EventTranslationInterface.php](./src/event/src/Domain/Model/EventTranslationInterface.php)

In the next step we should also define in our Domain which Exception could 
happen. At the begin of a project there will be not much Exception so we 
have here 2 expected Exceptions:

- [EventNotFoundException.php](./src/event/src/Domain/Exception/EventNotFoundException.php)
- [EventTranslationNotFoundException.php](./src/event/src/Domain/Model/EventTranslationNotFoundException.php)

The Domain Models should not be just some Entities with getter and setter 
they should implement also some kind of business logic. Mostly here is 
spoken about Rich Models, which can have complex logic in itself and also throw
exceptions when something unexpected happens.

Here a more complex example on a User Model:

```php
class User {
    private string $username;
    private string $email;
    private string $passwordHash;

    public function __construct(
        string $username,
        string $email,
        string $rawPassword,
        callable $passwordHasher
    ) {
        $this->changeUsernameAndEmail($username, $email);
        $this->changePassword($rawPassword, $passwordHasher);
    }

    public function changeUsernameAndEmail(string $username, string $email): void
    {
        if (str_contains($username, '@') && $username !== $email) {
            throw new \InvalidArgumentException('A username containing the @ symbol must much the given email address.');
        }

        $this->username = $username;
        $this->email = $email;
    }

    public function changePassword($rawPassword, callable $passwordHasher): void
    {
        $this->passwordHash = $passwordHasher($rawPassword);
    }
}
```

The advantage of rich models are also that they are valid objects after they 
are constructed. So the constructor of your model already defines all required 
attributes which the Model needs to be persisted and setters are only used 
for optional fields. Also it is common that not every field has a setter and 
some can so only be set at the beginning of creating the model, when example 
a field is not changeable like our `EventTranslation::locale`.

### 5.2. Defining Domain Services

The most common service which exist in the Domain is the ModelRepository. As 
the Repository should allow to save our Models in a persistence storage we 
need to create a Port for it. This Port is the RepositoryInterface which 
uses Dependency Inversion Principle so there is no direct relation to the 
persistence storage.

Another common service is the Domain is the ModelFactory to create an 
instance of your model. In our case there exist no ModelFactory as we did 
directly add a create and createTranslation method to our Repository. This is 
because of experience that how a model need to be constructed can depend on the
used persistence layer.

 - [EventRepositoryInterface.php](./src/event/src/Domain/Repository/EventRepositoryInterface.php)

The Interface will make use of our before defined EventNotFoundException in 
case of the `getOneBy` is called with none exist filter parameters. If 
carefully look at the defined RepositoryInterface you will see that there 
are 2 methods which look equal the `getOneBy` and `findOneBy`. Where in most 
cases the `getOneBy` where the Model is returned or a EventNotFoundException 
is thrown is the best. There are usecases where the `findOneBy` make more 
sense to make readable code. As an example: 

```php
// bad example for using getOneBy
try {
   $existUser = $this->userRepository->getOneBy(['username' => $username]);
   
   throw new UsernameAlreadyTakenException($username);
} catch (UserNotFoundException $e) {
   // expected program flow
}

$user = new User($username);

// ..
```

In this case the `findOneBy` is a better used the expected program flow is 
not going through an exception:

```php
// good example replacing getOneBy with findOneBy
$existUser = $this->userRepository->findOneBy(['username' => $username]);

if ($existUser) {
    throw new UsernameAlreadyTakenException($username);
}

$user = new User($username);

// ..
```

How a RepositoryInterface can be defined is an
[own topic](https://github.com/sulu/sulu/issues/5931). I personally 
prefer that the Interfaces look similar to each other instead of defining too 
specific methods. Still I would not create a common interface as it should 
just define the method you really require and inheritance always adds 
complexity.

With the created RepositoryInterface we did define our minimal domain setup.

## 6. Creating Application Layer

The minimal domain setup in the chapter before allows us now to create our 
application logic. The application layer provides the services for the 
outside which can be used. In our case we have decided that we want to use a 
command bus to communicate with our application. Because of conflicts of the 
name command with CLI command/scripts in widely used frameworks we have 
decided to go here instead with Command and CommandHandler with Message and 
MessageHandler.

### 6.1. Creating Messages

The messages or commands are at the end Data transfer objects (DTOs), which 
converts the given data into an accessible format for the handlers:

 - [CreateEventMessage.php](./src/event/src/Application/Message/CreateEventMessage.php)
 - [ModifyEventMessage.php](./src/event/src/Application/Message/ModifyEventMessage.php)
 - [RemoveEventMessage.php](./src/event/src/Application/Message/RemoveEventMessage.php)

All data should be injected into the message over the constructor and so the 
message should be immutable by design. It should only define getter methods to 
make data easier to access. The data given to the message should just use 
primitives types, this allows to create it from different contexts
(cli/http/...). Also no data should be needed to be parsed out like in 
arguments of a cli command. This type of things should happen in the 
UserInterface layer.

The primitives types not only allows to create the message at any case but 
also make it easier when using like in our case a $data array directly 
forwarding in example our $_POST data or in whatevery case your framework 
does provide this kind of data. Example in symfony with the http foundation 
request data:

```php
$message = new CreateEventMessage($request->request->all());
```

Where we did in our case created typical create, modify and remove messages. 
It is common that there exist more specific message which represent your 
domain. Example here could be a `ChangePasswordMessage` or a 
`PublishEventMessage`.

### 6.2. Validating Messages

The messages can be validated in different ways. It's possible to validate it 
directly in the constructor. If an unexpected data is given you can throw a 
InvalidArgumentException e.g.:

```php
Assert::string($data['title'] ?? null, 'Expected a valid title to be given.');
```

This is one of the simplest and easiest way for validation. Another way of 
validating the inputted data is adding a validating method to it. e.g.:

```php
public function validate(): void
{
    $errors = [];
    
    if (empty($this->data['title']) || !is_string($this->data['title'])) {
        $errors[] = [
            'field' => 'title',
            'message' => 'Expected a valid title to be given.',
        ];
    }
    
    if (empty($this->data['locale'])
        || !is_string($this->data['locale']) ||
        !in_array($this->data['locale'], ['en', 'de'])
     ) {
        $errors[] = [
            'field' => 'locale',
            'message' => 'Expected locale "en" or "de" given.',
        ];
    }
    
    throw new ValidationException($errors);
}
```

The advantage of this method is that you validate multiple inputted data and 
not just one by one. Instead of an exception you can also go with a boolean 
return:

```php
private ?bool $valid = null;
private array $errors = [];

public function valid(): bool
{
    // if the message is already validated we don't need to validate it again
    if ($this->valid !== null) {
        return $this->valid;
    }

    if (empty($this->data['title']) || !is_string($this->data['title'])) {
        $this->errors[] = [
            'field' => 'title',
            'message' => 'Expected a valid title to be given.',
        ];
    }

    if (empty($this->data['locale'])
        || !is_string($this->data['locale']) ||
        !in_array($this->data['locale'], ['en', 'de'])
     ) {
        $this->errors[] = [
            'field' => 'locale',
            'message' => 'Expected locale "en" or "de" given.',
        ];
    }
 
    $this->valid = empty($this->errors);
 
    return $this->valid;
}

public function getErrors(): array
{
    if (null === $this->valid) {
        throw new \LogicException('Run "valid" before calling "getErrors"');
    }

    return $this->errors;
}
```

All of the above methods I think are good ways for validations, another way 
would be using an own service which is validating the message. This 
specially make sense if the message can be extended with additional data 
which need to be validated.

```php
$message = new CreateEventMessage($data);
$validator->validate($message);
```

So there are different ways of validating of the message, you also always 
need to ask how much you want to validate inside your application core. In most 
cases I would go with the first solution by validating it directly in the 
constructor. If your controller would need another type of validation to 
show better error messages you could provide some json schema standard. 
This type of standard allows you that you can not only validate it in a 
controller but also the client could validate it before triggering your api. 

Where I personally prefer the pattern with to give an whole array into the 
message, it is very common that you design your message / command constructor 
with the data which is required:

```php
$message = new CreateEventMessage($locale, $title);
```

Which advantage of disadvantage this have we will see in the
[Make Handlers extendable](#64-make-handlers-extendable) chapter.

### 6.3. Creating Message Handlers

The message handlers implements what will happen when the message is 
dispatch. In our case they are responsible for creating, modifying and 
removing an entity.

As mention already in the [creating messages chapter](#61-creating-messages) 
its common that there are also more specific messages like 
`ChangePasswordMessage` or `PublishEventMessage`, which are modifying our 
objects not in the typical crud way.

For our typical handlers which we created the handler need the Repository. The 
repository of our models is represented in our Handlers only be the
RepositoryInterface, which make over the Dependency Inversion Principle
possible that different implementation or changeable implementation could be
behind it.

With the message we did already keep our handlers clean from request objects, 
sessions and other things which are only needed to receive or deliver 
something.

Also with the message object we already did make sure the data we are 
receiving are already validated. So we can here in our create and modify 
handler load the requested model and call the mappers to map directly the data 
to the model.

- [CreateEventMessageHandler.php](./src/event/src/Application/MessageHandler/CreateEventMessageHandler.php)
- [ModifyEventMessageHandler.php](./src/event/src/Application/MessageHandler/ModifyEventMessageHandler.php)
- [RemoveEventMessageHandler.php](./src/event/src/Application/MessageHandler/RemoveEventMessageHandler.php)

In the case of the Modify and Remove handler the identifier is represented 
by an array (Map). The decision here was to go instead of using `int $id` to 
go with an `array $identifier` to make it possible to modify or remove our 
model not only by its id but also by any other unique identifier a model 
could have. For example an uuid or an unique key field.

The handlers are in our case returning nothing or returning the object. They 
are not responsible how the object will be represented, this should not be 
part of them. The handlers can throw events which could trigger other 
processes like sending an email. Also other bounded contexts could listening 
to this events to update there data with the done changes.

### 6.4. Make Handlers extendable

The datamapper which we added to the CreateEventMessageHandler and 
ModifyEventMessageHandler make it possible that we don't need to duplicate 
the code to map data to our Model which are the same between created and 
modify.

- [EventDataMapper.php](./src/event/src/Application/MessageHandler/CreateEventMessageHandler.php)

But this is not the only advantage of it. If we are allowing multiple 
mappers we can make our create and modify handlers extendable. By 
introducing an interface for the mapping:

- [DataMapperInterface.php](./src/event/src/Application/Datamapper/EventDataMapperInterface.php)

Also have here a look at the so called [Visitor Pattern](https://en.wikipedia.org/wiki/Visitor_pattern)
which make this type of things possible.

The requirement for making it extendable was that our messages allows that 
additional data can be provided to our handler. This is done over the 
introduced getData method.

### 6.5 Third party code inside application

Where it is good to keep your application core small of dependency it doesn't 
mean that all your vendor code is automatically infrastructure code.

The connection to infrastructure code is mostly done over an Interface using 
the Dependency Inversion Principle. PHP provides over
[PSR](https://www.php-fig.org/psr/) many common interfaces where no own 
interface need to be written.

At the time I'm written this chapter Matthias Noback has released an article 
about this topic which is worse to read:
[https://matthiasnoback.nl/2021/08/on-using-psr-abstractions/](https://matthiasnoback.nl/2021/08/on-using-psr-abstractions/).

There are other common libraries which are for example abstractions 
like [Flysystem library](https://github.com/thephpleague/flysystem) for local
and remote filesystems access by
[Frank de Jonge](https://github.com/frankdejonge).
This type of libraries already provides interfaces you can use, mostly this 
interfaces are more bulletproof than if we would write our own. Also this 
kind of libraries can be inspiring how you can use the 
[Adapter pattern](https://flysystem.thephpleague.com/v2/docs/architecture/) to 
connect to your infrastructure code.

Also small libraries do not hurt to add to your application code, it should 
not have the need to reinvent the wheel, common libraries are example the 
[webmozart/assert](https://github.com/webmozarts/assert) which provides you 
a good way to valid the data given to the message constructor. Or a small 
library like the [ramsey/uuid](https://github.com/ramsey/uuid) to generate 
uuid4 strings for your application.

Things like how your code or library is integrated into a framework or how it 
is communicating with the database, orm should still definitely live in the 
infrastructure code.

## 7. Testing Application Core

A clean architecture, following the concepts of
[SOLID](https://en.wikipedia.org/wiki/SOLID) software engineering, specially 
[single responsibility](https://en.wikipedia.org/wiki/Single-responsibility_principle)
should make your code easier to test with the framework you want.

### 7.1. Mocks vs no Mocks

As there are Interfaces used to connect to your Infrastructure code, you 
will naturally create mocks for them. Where in somecases easier, you need to
know about the inner structure of the class / method you want to test. This is
something you want to avoid. The inner part of a method should be changable and
aslong as you don't change input or output of that method the tests should not 
be needed to be changed.

In case of the RepositoryInterface we created in the Domain the solution is 
that we provide a InMemoryRepository. This InMemoryRepository can also be a 
small reference how our repository methods should behave and return things 
beside the interface definition.

Things like PSR 3 for logs already provides functional working 
implementation like the NullLogger which can also be used in the tests.

If testing without mocks is used you maybe need inside your tests use an 
instance of your infrastructure code. As an example using the symfony event 
dispatcher as a psr 14 implementation. So there is no need to create here 
your own implementation of an interface only for testing.

I can recommend to give testing without mocks a try, atleast I would 
avoid mocking things like models, message in your tests. Creating an 
instance of them and using them directly does make your tests a lot more 
maintainable and avoid that an inner refractoring of a service requires to 
rewrite all your tests. More about this topic you can find on
[Frank de Jonge](https://github.com/frankdejonge) blog in the
[https://blog.frankdejonge.nl/testing-without-mocking-frameworks/](https://blog.frankdejonge.nl/testing-without-mocking-frameworks/).
article.

### 7.2. AAA-pattern

The [AAA pattern](https://medium.com/@pjbgf/title-testing-code-ocd-and-the-aaa-pattern-df453975ab80)
is a way how to write tests, in most cases you are doing this kind of things 
naturally. But knowing that there exist this pattern help you to avoid 
violeting it.

AAA stands for Arrange, Act, Assert. Which are the 3 steps you should write 
your test.

First you arrange all you need to test the method in our case for a test 
with the handler, the handler itself with the required repository and the 
message we want to test:

```php
// Arrange
$repository = new InMemoryEventRepository();
$handler = new CreateEventMessageHandler($repository);

$message = new CreateEventMessage(['locale' => 'en', 'title' => 'Test']);
```

After you arranged all you need you actually call what you want to test in 
the step of Act:

```php
// Act
$event = $handler->__invoke($message);
```

After this we finish the test with asserting the expected result.

```php
// Assert
$translation = $event->findTranslation('en');
$this->assertNotNull($translation);
$this->assertSame('en', $translation->getLocale());
$this->assertSame('Test', $translation->getTitle());
```

If doing this only once per test, the tests are very readable.
In combination with mock-free testing this makes the application testing code
readable and maintainable.

## 8. Creating Infrastructure Layer

The infrastructure provides implementation for different Interfaces which are 
accessed in our application code. The interface should make the 
Infrastructure replaceable and our application core should work without it. In 
the following examples it is shown how to implement different ORMs and how to
integrate our application code into different frameworks.

### 8.2. Creating Doctrine ORM Integration

Coming soon...

### 8.3. Creating Cycle ORM Integration

Coming soon...

### 8.4. ORM Datamapper vs Active Record

Coming soon...

### 8.5. Creating Symfony Integration

Coming soon...

### 8.6. Creating Spiral Integration

Coming soon...

## 9. Testing Infrastructure Layer

Coming soon...

### 9.1. Create reusable test cases

Coming soon...

### 9.2. Create reusable test traits

Coming soon...

## 10. Creating UserInterface Layer

Coming soon...

### 10.1. Create PSR 7 Controller

Coming soon...

## 11. Communicate between Bounded Contexts

Coming soon...

### 11.1 Process Manager

Coming soon...
   
## 11. Porting Application Core into another language

Coming soon...

## 00. Summary

Coming soon ...

 - Hexagonal Architecture
   - Layers (clear structure)
   - Dependency Inversion Principle (connection between layers)
   - Adapter Pattern (connection to infrastructure)
   - Visitor Pattern (extendability)
 ...

## 00.1 Thank you

At first, I want to thank here all my colleagues from Sulu without the I would
not be able to write this and all feedback they gave me. Specially here Thomas
Schedler which always pushing us to look "beyond the tellerand" ;). I also want
to thank here the spiral community which did help me a lot to integrate the Cycle
ORM integration as an alternative persistence layer. A lot of things which I
learned about Hexagonal architecture is coming from Matthias Noback blogs about
this topic. So I also want to thank him here to share his knowledge with us, 
that we can create sustainable software. Another man which was also an 
inspiration for me is Frank do Jonge, creator of flysystem and enthusiast of 
testing without mocking, which did show me a way of making tests more 
maintainable.

And at the end I want to thank you, the reader of this, thank you for taking
this time I hope you did find something useful in my "Project Rabbit Hole".

## 00.2 Links

 - https://web.archive.org/web/20170730135337/http://alistair.cockburn.us/Hexagonal+architecture
 - https://martinfowler.com/tags/domain%20driven%20design.html
 - https://en.wikipedia.org/wiki/Hexagonal_architecture_(software)
 - https://en.wikipedia.org/wiki/Visitor_pattern
 - https://en.wikipedia.org/wiki/SOLID
 - https://www.php-fig.org/psr/
 - https://en.wikipedia.org/wiki/Single-responsibility_principle
 - https://flysystem.thephpleague.com/v2/docs/architecture/
 - https://blog.frankdejonge.nl/testing-without-mocking-frameworks/
 - https://medium.com/@pjbgf/title-testing-code-ocd-and-the-aaa-pattern-df453975ab80
 - https://matthiasnoback.nl/tags/hexagonal%20architecture/
 - https://matthiasnoback.nl/2021/08/on-using-psr-abstractions/
 - https://matthiasnoback.nl/2020/02/is-all-code-in-vendor-infrastructure-code/
