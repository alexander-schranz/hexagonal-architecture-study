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
 - [11. Porting Application Core into another language](#11-porting-application-core-into-another-language)
 - [00. Summary](#00-summary)
 - [00.1 Thank you](#001-thank-you)

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
https://web.archive.org/web/20170730135337/http://alistair.cockburn.
us/Hexagonal+architecture.

Most which I'm familiar with is coming from the great Matthias Noback which
did write several articles about this topic which can be found on his website
https://matthiasnoback.nl/tags/hexagonal%20architecture/.

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

The main business logic should in this architecture exist in the 
`Application Core`, which we will split into the `Domain` and the 
`Application` namespace.

The rest, the adapters split into `UserInterface` and `Infrastructure` should 
be kept that it is easily replace them or add new ones:

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

So now we already did talk about the 4 Layers of the Hexagonal Architecture 
which are:

 - Application
 - Domain
 - Infrastructure
 - UserInterface

The Hexagonal Architecture defines a specific way which layers are allowed 
to access the models and services of other layers. This is shown in the 
previous shown graphic with the arrows.

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
Implementation.

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

Coming soon...

### 5.1. Modeling Domain Models

Coming soon...

### 5.2. Defining Domain Services

Coming soon...

## 6. Creating Application Layer

Coming soon...

### 6.1. Creating Messages

Coming soon...

### 6.2. Validating Messages

Coming soon...

### 6.3. Creating Message Handlers

Coming soon...

### 6.4. Make Handlers extendable

Coming soon...

## 7. Testing Application Core

### 7.1. Mocks vs no Mocks

TODO rewrite: 

To make the Application Core functional testable without any
Infrastructure specific code a InMemoryRepository can be provided. This make
your Domain testable without any mocking library which make code also
easier maintainable as if you are changing just internal functions you don't
need to change your test as long as the input and output is the same.

See also: https://blog.frankdejonge.nl/testing-without-mocking-frameworks/

### 7.2. AAA-pattern

Coming soon...

## 8. Creating Infrastructure Layer

Coming soon...

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

## 11. Porting Application Core into another language

## 00. Summary

Coming soon...

## 00.1 Thank you

At first, I want to thank here all my colleagues from Sulu without the I would
not be able to write this and all feedback they gave me. Specially here Thomas
Schedler which always pushing us to look "beyond the tellerand" ;). I also want
to thank here the spiral community which did help me a lot to integrate the Cycle
ORM integration as an alternative persistence layer. A lot of things which I
learned about Hexagonal architecture is coming from Matthias Noback blogs about
this topic so I also want to thank him here to share his knowledge with us, 
that we can create sustainable software.

And at the end I want to thank you, the reader of this, thank you for taking
this time I hope you did find something useful in my "Project Rabbit Hole".
