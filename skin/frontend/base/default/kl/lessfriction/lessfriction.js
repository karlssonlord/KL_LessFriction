/**
 * We'll need a bunch of global variables thanks to
 * how Magento is designed.
 */
var Checkout,
    checkout,
    PaymentMethod,
    payment,
    Review,
    review,
    ShippingMethod,
    shippingMethod,
    ShippingAddress,
    shippingAddress,
    BillingAddress,
    billingAddress;

(function() {

    /**
     * Checkout
     *
     * Enter short description here...
     */
    Checkout = Class.create();

    Checkout.prototype = {
        initialize: function(config) {
            this.isDeveloperMode = false;
            this.saveMethodUrl   = config.saveMethodUrl;
            this.available       = true;
            this._interval       = false;
            this._queue          = Array();
        },

        _onSectionClick: function(event) {

        },

        queueRequest: function(url, options) {
            this._queue.push($H({url: url, options: options, timstamp: +new Date()}));

            if (!this._interval) {
                this.ajaxRequest();
            }
        },

        ajaxRequest: function() {
            this.log("Set interval");

            this._interval = setInterval(function() {

                /**
                 * The checkout is available for handling requests
                 * and there's requests in the queue
                 **/
                if (this.available && this._queue.length > 0) {
                    this.available = false;

                    var queueItem = this._queue.shift();
                    var url       = queueItem.get('url');
                    var options   = queueItem.get('options');
                    var that      = this;

                    if (typeof options.onSuccess === 'undefined') {
                        options.onSuccess = function(result) {};
                    }

                    options.onSuccess = (function() {
                        var cache = options.onSuccess;

                        return function(result) {
                            cache.apply(this, arguments);

                            this.log(result.responseJSON.blocks);

                            if (result.responseJSON.redirect) {
                                location.href = result.responseJSON.redirect;
                                return;
                            }

                            for (var name in result.responseJSON.blocks) {
                                var sectionContainers = $$('.' + name + '-section');
                                result.responseJSON.blocks[name] += '<div class="overlay" style="display:none"></div>';

                                sectionContainers.invoke(
                                    'update',
                                    result.responseJSON.blocks[name]
                                );

                                sectionContainers.each(function (el) {
                                    new Effect.Highlight(el, {
                                        duration: 0.5
                                    });
                                });
                            }

                            that.available = true;
                        }.bind(that);
                    }(that));

                    var request = new Ajax.Request(url, options);

                /**
                 * There's no requests in the queue...
                 **/
                } else if (this._queue.length == 0) {
                    this.log('Clear interval');

                    clearInterval(this._interval);
                    this._interval = false;
                    return;
                /**
                 * Checkout is not available for handling requests...
                 **/
                } else {
                    this.log("Pending AJAX request...");
                }
            }.bind(this), 250);
        },

        ajaxFailure: function() {
            // TODO: Unset all spinners/overlays
        },

        reloadProgressBlock: function(toStep) {

        },

        setMethod: function(method) {
            if (method != 'guest' && method != 'register') {
                var message = Translator.translate('Please choose to register or to checkout as a guest').stripTags();
                alert(message);
                return false;
            }

            this.log(method);

            if (method == 'guest') {
                Element.hide('register-customer-password');
            } else if(method == 'register') {
                Element.show('register-customer-password');
            }

            this.ajaxRequest(
                this.saveMethodUrl,
                {
                    method:     'post',
                    // onFailure: this.ajaxFailure.bind(this),
                    parameters: { method: method },
                    onSuccess:  function() {
                        $$('.login-section').invoke('hide')
                    }
                }
            );

            this.method = method;
            document.body.fire('login:setMethod', {method : this.method});
        },

        log: function(message) {
            if (this.isDeveloperMode && window.console) {
                console.log(message);
            }
        }
    };



    /**
     * Section
     *
     * The checkout consists of multiple sections,
     * steps or block – this is the boilerplate class
     * for them.
     */
    var Section = Class.create({
        func: $H({
            'before': $H({
                'init':     $H({}),
                'validate': $H({}),
            }),
            'after': $H({
                'init':     $H({}),
                'validate': $H({})
            })
        }),

        addFunc: function(position, method, code, func) {
            var position = this.func.get(position);

            if (!position.get(method)) {
                position.set(method, $H({code: func}));
            } else {
                position.get(method).set(code, func);
            }
        },

        execFunc: function(position, method) {
            (this.func.get(position).get(method)).each(function(func) {
                (init.value)();
            });
        },

        request: function(url, options) {
            var request = new Ajax.Request(url, options);
            return request;
        },

        /**
         * Initialize class
         */
        initialize: function(config) {
            this._config       = config;
            this.saveUrl       = false;
            this.form          = false;
            this.onComplete    = this.resetLoadWaiting.bindAsEventListener(this);
            this.onSuccess     = this.nextStep.bindAsEventListener(this);;
            this.onFailure     = false;
            this.requestMethod = 'post';

            this.init();
        },

        /**
         * Short hands for inserting code to be run
         * before and after local init function
         */
        addBeforeInitFunction: function(code, func) {
            this.addFunc('before', 'init', code, func);
        },
        addAfterInitFunction: function(code, func) {
            this.addFunc('after', 'init', code, func);
        },
        beforeInit: function() {
            this.execFunc('before', 'init');
        },
        afterInit: function() {
            this.execFunc('after', 'init');
        },

        /**
         * Local initialization
         */
        init: function() {
            this.beforeInit();

            this.afterInit();
        },

        /**
         * Short hands for inserting code to be run
         * before and after local validate function
         */
        addBeforeValidateFunction: function(code, func) {
            this.addFunc('before', 'validate', code, func);
        },
        addAfterValidateFunction: function(code, func) {
            this.addFunc('after', 'validate', code, func);
        },
        beforeValidate: function() {
            var validateResult = true;
            var hasValidation  = false;

            (this.func.get('before').get('validate')).each(function(validate) {
                hasValidation = true;

                if ((validate.value)() == false) {
                    validateResult = false;
                }
            }.bind(this));

            if (!hasValidation) {
                validateResult = false;
            }

            return validateResult;
        },
        afterValidate: function() {
            this.execFunc('after', 'validate');
        },

        validate: function() {
            return true;
        },

        /**
        * Validation
        */
        _validate: function() {
            var result = this.beforeValidate();

            if (result === true) {
                return true;
            }

            result = this.validate();
            if (result === true) {
                return true;
            }

            result = this.afterValidate();
            if (result === true) {
                return true;
            }

            return false;
        },

        /**
         * Save
         */
        save: function() {
            checkout.log('Save section');

            if (this._validate()) {
                if (this._config.form) {
                    var params  = Form.serialize(this._config.form);
                } else {
                    var params  = false;
                }

                if (this._config.relations) {
                    if (params) {
                        params += '&';
                    }

                    this.setLoadingBlocks();
                    params += 'relations=' + this._config.relations.toString();
                }

                console.log(params);

                var options = {
                    method:     this.requestMethod,
                    onComplete: this.onComplete,
                    onSuccess:  this.onSuccess,
                    onFailure:  this.onFailure,
                    parameters: params
                };

                checkout.queueRequest(this._config.saveUrl, options);
            }
        },

        setLoadingBlocks: function() {
            for (var i = 0; i < this._config.relations.length; i++) {
                console.log('.' + this._config.relations[i] + '-section');
                $$('.' + this._config.relations[i] + '-section').each(function(section) {
                    var overlay = section.addClassName('loading').down('.overlay');
                    if (overlay) overlay.show();
                });
            }
        },

        resetLoadingBlocks: function() {
            // TODO: Make sure no ajax-request is running
            for (var i = 0; i < this._config.relations.length; i++) {
                $$('.' + this._config.relations[i] + '-section').each(function(section) {
                    var overlay = section.removeClassName('loading').down('.overlay');
                    if (overlay) overlay.hide();
                });
            }
        },

        /**
         * Reset load waiting
         */
        resetLoadWaiting: function(transport) {
            console.log('resetLoadWaiting');
            this.resetLoadingBlocks();
        },

        /**
         * Next step
         */
        nextStep: function(transport) {
            if (transport && transport.responseText) {
                try {
                    response = eval('(' + transport.responseText + ')');
                } catch (e) {
                    response = {};
                }
            }

            if (transport.responseJSON.success) {
                location.href = this._config.successUrl;
                return;
            }

            if (response.error) {
                alert(response.message);
                return false;
            }
        }
    });



  /**
   * Shipping Method
   *
   * Enter short description here...
   */
  ShippingMethod  = Class.create(Section, {
    validate: function() {
      return true;
    },
  });



  /**
   * Payment Method
   *
   * Enter short description here...
   */
  PaymentMethod   = Class.create(Section, {
    switchMethod: function(method) {
      checkout.log(method);
      this.save();
    },

    /**
     * Validation
     */
    validate: function() {
        var methods = document.getElementsByName('payment[method]');

        if (methods.length == 0) {
            checkout.setMessage((Translator.translate('Your order cannot be completed at this time as there is no payment methods available for it.').stripTags()), 'error');
            return false;
        }

        for (var i = 0; i < methods.length; i++) {
            if (methods[i].checked) {
                return true;
            }
        }
    },
  });



  /**
   * Address
   *
   * Since the checkout consists of two types of addresses,
   * we create a general address class to keep the shared
   * methods in one place.
   */
  var Address         = Class.create(Section, {
    init: function() {
      this.beforeInit();

      if ($(this._config.form).hasClassName('primary')) {
        $(this._config.form).up(1).hide();
      }

      $(this._config.form).getElements().invoke('observe', 'keyup', function(e) {
          var element = Event.element(e);

          if (this.keyTimeout) {
            clearTimeout(this.keyTimeout);
          }

          this.keyTimeout = setTimeout(function() {
            console.log('Try to save address');
            this.save();
          }.bind(this), 500);
      }.bind(this));

      this.afterInit();
    },
    validate: function() {
      var validator      = new SectionValidation(this._config.form);

      if (validator.validate()) {
        return true;
      } else {
        return false;
      }
    },
    toggleNewAddressForm: function(form, isNew) {
      if (isNew === true) {
        this.resetSelectedAddress();
        form.show();
      } else {
        form.hide();
      }
    },
    resetSelectedAddress: function(form) {
      this.selectAddressElement.invoke('setValue', '');
    }
  });



  /**
   * Shipping Address
   *
   * Enter short description here...
   */
  ShippingAddress = Class.create(Address, {
  });

      var placeOrder = document.on(
          'click',
          '[name="shipping[use_for_billing]"]',
          function(event, element) {
              var form = $('co-billing-form');
              if (form) {
                form.up(1).toggle();
              }
          }.bind(this)
      );

  /**
   * Billing Address
   *
   * Enter short description here...
   */
  BillingAddress  = Class.create(Address, {});



  /**
   * Review
   *
   * Enter short description here...
   */
  Review = Class.create(Section, {
    init: function() {
      /**
       * Add event listener for clicking the
       * place order button
       */
      var placeOrder = document.on(
          'click',
          '.btn-checkout',
          function(event, element) {
              element.disabled = true;
              this.save();
              Event.stop(event);
          }.bind(this)
      );
    }
  });



  var SectionValidation       = Class.create();
  SectionValidation.prototype = new Validation;
  Object.extend(SectionValidation, Validation);

  SectionValidation.prototype.validate = function() {
    var result    = false;
    var useTitles = this.options.useTitles;
    var callback  = this.options.onElementValidate;

    try {
        if (this.options.stopOnFirst) {
            result = Form.getElements(this.form).all(function(elm) {
                if (elm.hasClassName('local-validation') && !this.isElementInForm(elm, this.form)) {
                    return true;
                }
                return SectionValidation.validate(elm,{useTitle : useTitles, onElementValidate : callback});
            }, this);
        } else {
            result = Form.getElements(this.form).collect(function(elm) {
                if (elm.hasClassName('local-validation') && !this.isElementInForm(elm, this.form)) {
                    return true;
                }
                return SectionValidation.validate(elm,{useTitle : useTitles, onElementValidate : callback});
            }, this).all();
        }
    } catch (e) {
        // Fail silently
    }

    if (!result && this.options.focusOnError) {
        try{
            Form.getElements(this.form).findAll(function(elm) {
                return $(elm).hasClassName('validation-failed')
            }).first().focus()
        } catch(e) {
            // Fail silently
        }
    }

    this.options.onFormValidate(result, this.form);
    return result;
  }

  Object.extend(SectionValidation, {
      validate : function(elm, options){
          options = Object.extend({
              useTitle : false,
              onElementValidate : function(result, elm) {}
          }, options || {});
          elm = $(elm);

          var cn = $w(elm.className);
          return result = cn.all(function(value) {
              var test = SectionValidation.test(value,elm,options.useTitle);
              options.onElementValidate(test, elm);
              return test;
          });
      },
      test: function(name, elm, useTitle) {
          var v = Validation.get(name);
          var prop = '__advice'+name.camelize();
          try {
              if(Validation.isVisible(elm) && !v.test($F(elm), elm)) {

                  this.updateCallback(elm, 'failed');

                  elm[prop] = 1;

                  return false;
              } else {
                  this.updateCallback(elm, 'passed');
                  elm[prop] = '';

                  return true;
              }
          } catch(e) {
              throw(e)
          }
      }
  });
})();