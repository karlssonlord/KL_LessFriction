var Checkout, 
    checkout,
    PaymentMethod,
    payment,
    Review,
    review,
    ShippingMethod,
    shippingMethod;

(function() {
  Checkout = Class.create();

  Checkout.prototype = {
    initialize: function(config) {
      this.isDeveloperMode = false;
      this.saveMethodUrl   = config.saveMethodUrl;
      this.available       = true;
      this._interval        = false;
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
          if (this.available && this._queue.length > 0) {
              this.available = false;

              var queueItem = this._queue.shift();
              var url       = queueItem.get('url');
              var options   = queueItem.get('options');

              if (typeof options.onSuccess === 'undefined') {
                options.onSuccess = function(result) {};
              }

              var that = this;

              options.onSuccess = (function() {
                  var cache = options.onSuccess;

                  return function() {
                      cache.apply(this, arguments);
                      that.available = true;
                  }.bind(that);
              }(that));

              var request = new Ajax.Request(url, options);
          } else if (this._queue.length == 0) {
            this.log('Clear interval')
            clearInterval(this._interval);
            this._interval = false;
            return;
          } else {
              this.log("Pending AJAX request...");
          }
      }.bind(this), 250);
    },

    ajaxFailure: function() {

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
        // Element.hide('register-customer-password');
      } else if(method == 'register') {
        // Element.show('register-customer-password');
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
      if (this.isDeveloperMode) {
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
      'after':  $H({
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

    /**
     * Validation
     */
    validate: function() {
      var result = this.beforeValidate();
      if (result) {
        return true;
      }

      result = this.afterValidate();
      if (result) {
        return true;
      }

      return false;
    },

    /**
     * Save
     */
    save: function() {
      if (this.validate()) {
        var params  = Form.serialize(this._config.form);
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

    /**
     * Reset load waiting
     */
    resetLoadWaiting: function(transport) {
      console.log('resetLoadWaiting');
    },

    /**
     * Next step
     */
    nextStep: function(transport) {
      if (transport && transport.responseText){
        try {
          response = eval('(' + transport.responseText + ')');
        } catch (e) {
          response = {};
        }
      }

      if (response.error) {
        alert(response.message);
        return false;
      }
    }
  });

  ShippingMethod  = Class.create(Section, {});
  PaymentMethod   = Class.create(Section, {
    switchMethod: function(method) {
      checkout.log(method);
      this.save();
    }
  });

  /**
   * Address
   *
   * Since the checkout consists of two types of addresses,
   * we create a general address class to keep the shared
   * methods in one place.
   */
  var Address         = Class.create(Section, {
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

  var ShippingAddress = Class.create(Address, {});
  new ShippingAddress;
  var BillingAddress  = Class.create(Address, {});
  new BillingAddress;

  Review = Class.create(Section, {});
})();