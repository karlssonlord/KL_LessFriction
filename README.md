# Less Friction Checkout

##### A Magento module from Karlsson & Lord

## Features

* Hide login prompt
* Flexible layout
* Skip shipping method step
* Skip payment method step
* Newsletter signup checkbox

## Install

The easiest way to install the module is by using [modman](https://github.com/karlssonlord/modman):

1. `modman clone git@github.com:karlssonlord/KL_LessFriction.git`
2. `modman deploy KL_LessFriction`
3. Clear the cache
4. Check out


## Get started

### Configure

You probably want to check the settings under the following sections in Magento admin:

1. System &rarr; Configuration &rarr; Less Friction
2. System &rarr; Configuration &rarr; Newsletter &rarr; Checkout

#### Options

* `saveMethodUrl` - Endpoint URL
* `defaultMethod` - `guest`, `register` or `false`

#### General

#### Cart

#### Cross sell

#### Login

#### Addresses

#### Shipping methods

#### Payment methods

#### Section order

### Implement

#### Styling

Every loading block (section) in the checkout will have the class `.loading` applied to it. Also a `.overlay` is shown in each loading block.

##### Idle

```html
<div class="review-section">
    …
    <div class="overlay" style="display: none;"></div>
</div>
```

##### Loading

```html
<div class="review-section loading">
    …
    <div class="overlay"></div>
</div>
```

## Authors

* Andreas Karlsson
* Erik Eng
* Jacob Klapwijk
* Robert Lord
