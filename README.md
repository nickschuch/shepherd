UA - D8
=======

The University of Adelaide project.

## Requirements

* Vagrant 1.6+ (+ Plugins) - http://docs.vagrantup.com/v2/installation
* Virtualbox - https://www.virtualbox.org/wiki/Downloads

**Install Vagrant plugins**

Run the following via the command line:

```bash
# Virtualbox support.
$ vagrant plugin install vagrant-vbguest

# Automatically assigns an IP address.
$ vagrant plugin install vagrant-auto_network

# Adds "/etc/hosts" (local DNS) records.
$ vagrant plugin install vagrant-hostsupdater

# S3 bucket support.
$ vagrant plugin install vagrant-s3auth
```

**Add S3 account details**

Vagrant images are stored privately on Amazon S3. To pull down the images for the first time
you will need to add some credentials to your local profile.

Add the following to your "~/.profile" file.

```bash
export AWS_ACCESS_KEY_ID=AKIAIHQDNUJMSQIJXUDQ
export AWS_SECRET_ACCESS_KEY=D5yj5exudn7F2ccWQcmJyVtCMGvJc1U4XAZClQkH
```

NOTE: These credentials can only access the box images (cannot write or view other buckets).

## Getting started

**1) Start the VM.**

```bash
$ vagrant up
```

All commands from here are to be run within the VM. This can be done via the command:

```bash
$ vagrant ssh
```

This will take you to the root of the project **inside** of the vm.

**2) Pull down the dependencies**

```bash
$ composer install --prefer-dist --dev
```

**3) Build the project**

```bash
$ phing
```

The default build task is to build the project. For a list of tasks that can be run:

```bash
$ phing -l
```

**4) Go to the site**

The site can be found on the domain:

```
http://ua.dev
```

## Updating VM

For details please see:

https://github.com/previousnext/ua-dev#updating

## Deploy

**Note: These commands are also covered in the .pnxci.yml file**

### Setup

* Bundler is used to install ruby gems.
* Capistrano (Ruby Gem) is leveraged for deployments.

```bash
$ bundle install --path vendor/bundle
```


For front-end development and generating the styleguide, we use Gulp which
requires Node.js.

Download Node.js (e.g. via homebrew) or directly via https://nodejs.org/download/

To install required node modules:

```bash
$ npm install
```

### Compiling CSS and Styleguide

To compile CSS and build the styleguide run:

```bash
$ gulp
```

The styleguide is generated into the /styleguide directory. Open the index.html
to see it.

To see a list of gulp commands, type:

```bash
$ gulp -T
```

### Deploy QA

```bash
$ bundle exec cap dev deploy
```

### Deploy Staging

```bash
$ bundle exec cap staging deploy
```

## Anatomy of the project

### Directories

* **app** - This is the build artefact generated by the Phing build system. This gets delete on each run.
* **bin** - The tools installed by Composer and other technologies. You can access these tools globally within the Vagrant machine.
* **build** - Testing artefacts eg. PHPCS reporting.
* **modules** - Custom modules. These get symlinked into the **app** directory.
* **themes** - Custom themes. These get symlinked into the **app** directory.
* **vendor** - This is the code that goes along with the **bin** directories tools.

### Files

* **.pnxci.yml** - The CI and CD build system file. This file gets run at the time for testing and deployment.
* **build.xml** - The Phing build file. This declares all the build steps for the project.
* **composer.json** - Project dependencies.
* **provision.sh** - Additional steps that can be taken to provision the Vagrant VM.
* **ua.make** - The projects Drush make file. This locks in versions (especially Drupal core).
* **Vagrantfile** - The Vagrant VM's configuration.
