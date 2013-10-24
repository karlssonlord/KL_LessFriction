/**
 * We'll need a bunch of global variables thanks to
 * how Magento is designed.
 *
 * TODO: Do we really?
 */
var Checkout,
    checkout,
    Cart,
    cart,
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
     * Enable inline validation hints (this is default in enterprise)
     *
     */
    Validation.defaultOptions.immediate = true;
    Validation.defaultOptions.addClassNameToContainer = true;

    /**
     * Checkout
     *
     * TODO: Enter short description here...
     */
    Checkout = Class.create();

    Checkout.prototype = {
        initialize: function(config) {
            this._config         = config;
            this.isDeveloperMode = false;
            this.saveMethodUrl   = config.saveMethodUrl;
            this.available       = true;
            this._interval       = false;
            this._queue          = Array();
            this.defaultMethod   = config.defaultMethod;

            if ($$('.login-section').length == 0) {
                if (this.defaultMethod !== false) {
                    this.setMethod(this.defaultMethod);
                }
            }
        },

        /**
         * TODO: Purpose/Idéa?
         *
         */
        _onSectionClick: function(event) {

        },

        /**
         * Displaying a section as "loading" based on it's relations
         *
         */
        _setLoadingSections: function(relations) {
            if(typeof relations !== 'undefined' && relations.length > 0) {
                for (var i = 0; i < relations.length; i++) {
                    $$('.' + relations[i] + '-section').each(function(section) {
                        var overlay = section.addClassName('loading').down('.overlay');
                        if (overlay) overlay.show();
                    });
                }
            }
        },

        /**
         * Resetting loading sections from "loading" states
         *
         */
        _resetLoadingSections: function() {
            $$('[class*="-section loading"]').each(function(section) {
                var overlay = section.removeClassName('loading').down('.overlay');
                if (overlay) overlay.hide();
            });
        },

        /**
         * Backend request queueing
         *
         */
        queueRequest: function(url, options, config) {
            this._queue.push($H({
                url: url,
                options: options,
                timstamp: +new Date(),
                config: config
            }));

            if (!this._interval) {
                this.ajaxRequest();
            }
        },

        ajaxRequest: function() {
            this.log('Set interval');

            // TODO: Is this definition not too deep? Why not parallel to ajaxRequest?
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
                    var config    = queueItem.get('config');
                    var that      = this;


                    // Set all loading sections for this queue item
                    if (typeof config !== 'undefined') {
                        checkout._setLoadingSections(config.relations);
                    }

                    if (typeof options === 'undefined') {
                        options = {};
                    }

                    if (typeof options.onSuccess === 'undefined') {
                        options.onSuccess = function(result) {};
                    }

                    options.onSuccess = (function() {
                        var cache = options.onSuccess;

                        return function(result) {
                            cache.apply(this, arguments);

                            this.log(result.responseJSON.blocks);
                            this.log(result.responseJSON);

                            if (result.responseJSON.force_method) {
                                this.available = true;
                                if (result.responseJSON.force_method == 1) {
                                    this.oldMethod = this.method;
                                    this.setMethod(this.oldMethod);
                                } else {
                                    this.oldMethod = this.method;
                                    this.setMethod(result.responseJSON.force_method);
                                }

                                return;
                            }

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

                                if (this.isDeveloperMode) {
                                    sectionContainers.each(function (el) {
                                        new Effect.Highlight(el, {
                                            duration: 0.5
                                        });
                                    });
                                }
                            }

                            that.available = true;
                        }.bind(that);
                    }(that));

                    // TODO: Only make request if !== with last one, to handle unnecessary requests
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
                    // TODO: This is thrown irregularly, don't know why
                    this.log('Pending AJAX request...');
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
                alert(message); // TODO: This is generally a bad idea.
                return false;
            }

            this.log(method);

            if (method == 'guest') {
                Element.hide('register-customer-password');
            } else if(method == 'register') {
                Element.show('register-customer-password');
            }

            this.queueRequest(
                this.saveMethodUrl,
                {
                    method:     'post',
                    // onFailure: this.ajaxFailure.bind(this),
                    parameters: { method: method },
                    onSuccess:  function() {
                        $$('.login-section').invoke('hide');
                    }
                }
            );

            this.method = method;
            document.body.fire('login:setMethod', {method : this.method});
        },

        setMessage: function(text, type) {
            var messageHtml = "<ul><li class=\"" + type + "-msg\"><ul><li><span>" + text + "</span></li></ul></li></ul>";
            $('co-messages').update(messageHtml);
        },

        log: function() {
            if (this.isDeveloperMode && window.console && arguments) {
                console.log(arguments);
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
         *
         * Calling init()
         */
        initialize: function(config) {
            this._config       = config;
            this.saveUrl       = false;
            this.form          = false;
            this.onComplete    = this.resetLoadWaiting.bindAsEventListener(this);
            this.onSuccess     = this.nextStep.bindAsEventListener(this);
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
         * Local (generic) Section initialization
         *
         * This is called if method is not declared in each section.
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

                if ((validate.value)() == false) { // TODO: This is calling the *Address.setEmail(), woot?!
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
         * Generic Section Validation
         *
         *
         */
        _validate: function() {
            this.beforeValidate(); // Run added methods before validating section

            if (this.validate() === true) {
                this.afterValidate();
                return true;
            }
            else {
                this.afterValidate();
                return false;
            }
        },

        /**
         * Save Section
         *
         * TODO: Describe my purpose plz.
         */
        save: function() {
            checkout.log('Saving section', this);

            if (this._validate()) {
                if (this._config.form) {
                    var params  = Form.serialize(this._config.form);
                } else {
                    var params  = false;
                }

                if (this._config.relations) {
                    if (params) params += '&';
                    params += 'relations=' + this._config.relations.toString();
                }

                checkout.log(params);

                var options = {
                    method:     this.requestMethod,
                    onComplete: this.onComplete,
                    onSuccess:  this.onSuccess,
                    onFailure:  this.onFailure,
                    parameters: params
                };

                checkout.queueRequest(this._config.saveUrl, options, this._config);
            }
        },

        /**
         * Reset load waiting
         *
         * TODO: This is triggered by Section.onComplete, what's the idea here?
         */
        resetLoadWaiting: function(transport) {
            checkout.log('resetLoadWaiting');
        },

        /**
         * Next step
         *
         * TODO: Describe me..
         */
        nextStep: function(transport) {

            // Clearing any loading sections due to this request
            checkout._resetLoadingSections();

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

            /**
             * These messages are currently only used by the coupon handling
             * as far as I know.
             */
            if (response.messages) {
                response.messages.each(function(message) {
                    checkout.setMessage(message.text, message.type);
                });
            }

            if (response.error) {
                if (response.message) {
                    alert(response.message); // TODO: This is generally a bad idea.
                } else {
                    if (response.error_messages) {
                        alert(response.error_messages); // TODO: This is generally a bad idea.
                    } else {
                        alert(response.error); // TODO: This is generally a bad idea.
                    }
                }

                // TODO: Should be triggered by events?
                // checkout._resetLoadingSections();


                return false;
            }
        }
    });

    Cart = Class.create(Section, {
        init: function() {
            this.beforeInit();
            var that = this;

            var increaseOrDecreaseQty = document.on(
                'click',
                '.increaseQty,.decreaseQty',
                function(event, element) {
                    checkout.queueRequest(element.href, {}, that._config);
                    Event.stop(event);
                }.bind(this)
            );
            increaseOrDecreaseQty.stop();
            increaseOrDecreaseQty.start();

            var updateProductQty = document.on(
                'change',
                'input.qty',
                function(event, element) {
                    this.save();
                    Event.stop(event);
                }.bind(this)
            );
            updateProductQty.stop();
            updateProductQty.start();

            var removeProduct = document.on(
                'click',
                '#shopping-cart-table .btn-remove',
                function(event, element) {
                    checkout.queueRequest(element.href, {}, that._config);
                    element.up('tr').remove();
                    Event.stop(event);
                }.bind(this)
            );
            removeProduct.stop();
            removeProduct.start();

            var addDiscount = document.on(
                'submit',
                '#discount-coupon-form',
                function(event, element) {
                    this.handleCoupon();
                    Event.stop(event);
                }.bind(this)
            );
            addDiscount.stop();
            addDiscount.start();

            this.afterInit();
        },
        handleCoupon: function() {
            var form      = $('discount-coupon-form');
            var validator = new Validation(this._config.form);

            if (validator.validate()) {
                var params = Form.serialize(form);

                checkout.queueRequest(
                    this._config.couponUrl,
                    {
                        parameters: params,
                        onSuccess: this.nextStep.bindAsEventListener(this)
                    },
                    this._config
                );
            }
        },
    });

    /**
     * Shipping Method
     *
     * Enter short description here...
     */
    ShippingMethod  = Class.create(Section, {
        validate: function() {
            return false;
        }
    });



    /**
     * Payment Method
     *
     * Enter short description here...
     */
    PaymentMethod   = Class.create(Section, {
        switchMethod: function(method) {
            checkout.log(method);

            // Don't do anything if things are the same as before
            if(method === this.currentMethod) {
                return;
            }

            if (this.currentMethod && $('payment_form_'+this.currentMethod)) {
                this.changeVisible(this.currentMethod, true);
                $('payment_form_'+this.currentMethod).fire('payment-method:switched-off', {method_code : this.currentMethod});
            }
            if ($('payment_form_'+method)){
                this.changeVisible(method, false);
                $('payment_form_'+method).fire('payment-method:switched', {method_code : method});

                $(this._config.form).getElements().invoke('observe', 'keyup', function(e) {
                    var element = Event.element(e);

                    if (this.keyTimeout) {
                        clearTimeout(this.keyTimeout);
                    }

                    this.keyTimeout = setTimeout(function() {
                        checkout.log('Try to save address 1');
                        this.save();
                    }.bind(this), 500);
                }.bind(this));
            } else {
                // Event fix for payment methods without form like "Check / Money order"
                document.body.fire('payment-method:switched', {method_code : method});
            }
            if (method) {
                this.lastUsedMethod = method;
            }

            this.currentMethod = method;
            this.save();
        },

        changeVisible: function(method, mode) {
            var block = 'payment_form_' + method;
            [block + '_before', block, block + '_after'].each(function(el) {
                element = $(el);
                if (element) {
                    element.style.display = (mode) ? 'none' : '';
                    element.select('input', 'select', 'textarea', 'button').each(function(field) {
                        field.disabled = mode;
                    });
                }
            });
        },

        /**
         * Validation
         */
        validate: function() {
            var methods   = document.getElementsByName('payment[method]');

            var silentValidator = new SectionValidation(this._config.form);
            var validator = new Validation(this._config.form);

            if (!silentValidator.validate()) {
                return false;
            }

            validator.reset();

            if (!validator.validate()) {
                return false;
            }

            if (methods.length == 0) {
                checkout.setMessage(
                    Translator.translate('Your order cannot be completed at this time as there is no payment methods available for it.').stripTags(),
                    'error'
                );

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

            /**
             * TODO: This solution is naive and expects that the adresses
             * are alone in separate sections
             */
            if (!$(this._config.form).hasClassName('primary')) {
                $(this._config.form).up().hide();  // TODO: This should use a setting, not a relative element
            }

            // Setting up event handles
            $(this._config.form).getElements().invoke('observe',
                'keyup', this._saveSectionEvent.bind(this));

            $(this._config.form).getElements().invoke('observe',
                'change', this._saveSectionEvent.bind(this));

            this.afterInit();
        },
        // TODO: Should we really try to save for every event?
        _saveSectionEvent: function(e) {
            var element = Event.element(e);

            // If change in Klarna form, do nothing. TODO: This is ugly
            if(element.readAttribute('name') === 'pno') {
                return;
            }

            if (this.keyTimeout) {
                clearTimeout(this.keyTimeout);
            }

            this.keyTimeout = setTimeout(function() {
                checkout.log('Try to save section', this);
                this.save();
            }.bind(this), 500);
        },
        validate: function() {
            var validator      = new SectionValidation(this._config.form);
            if (validator.validate()) {
                delete validator;
                return true;
            } else {
                delete validator;
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
        inject: function(data, clone) {
            data.each(function (pair) {
                $$('[name="' + pair.key + '"]').first().setValue(pair.value);
            });
        },
        customerEmailExists: function(email) {
            checkout.queueRequest(
                this._config.customerEmailExistsUrl,
                {
                    parameters: 'email=' + email,
                    onSuccess:  this.onSuccess
                }
            );
        }
    });



    /**
     * Shipping Address
     *
     * TODO: Enter short description here...
     */
    ShippingAddress = Class.create(Address, {
        resetSelectedAddress: function(form) {
            var selectElement = $('shipping-address-select')
            if (selectElement) {
                selectElement.value = '';
            }
        },
        newAddress: function(value) {
            if (value) {
                this.resetSelectedAddress();
                Element.show('shipping-new-address-form');
                document.body.fire('shipping-address:show', {});
                checkout.log('Reset selected address and show form.');
            } else {
                Element.hide('shipping-new-address-form');
                document.body.fire('shipping-address:hide', {});
                checkout.log('Hide form.');
            }
        }
    });

    // TODO: This belongs to? Not used, yet.
    var useForBilling = document.on(
        'click',
        '[name="shipping[use_for_billing]"]',
        function(event, element) {
            var form = $('co-billing-form');
            if (form) {
                form.up().toggle(); // TODO: This should use a setting, not a relative element
            }
        }.bind(this)
    );

    /**
     * Billing Address
     *
     * TODO: Enter short description here...
     */
    BillingAddress  = Class.create(Address, {
        setEmail: function() {
            if ($('billing:email') && $('shipping:email')) {
                $('billing:email').value = $('shipping:email').value;
            };
            return // something thats a validation boolean true/false
        },

        beforeInit: function(){
            this.addBeforeValidateFunction('billing', this.setEmail);
        },
        resetSelectedAddress: function(form) {
            var selectElement = $('billing-address-select')
            if (selectElement) {
                selectElement.value = '';
            }
        },
        newAddress: function(value) {
            if (value) {
                this.resetSelectedAddress();
                Element.show('billing-new-address-form');
                checkout.log('Reset selected address and show form.');
            } else {
                Element.hide('billing-new-address-form');
                checkout.log('Hide form.');
            }
        }
    });

    // TODO: But why is this in the checkout global scope? Not used, yet.
    var useForShipping = document.on(
        'click',
        '[name="billing[use_for_shipping]"]',
        function(event, element) {
            var form = $('co-shipping-form');
            if (form) {
                form.up(1).toggle();
            }
        }.bind(this)
    );

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
                    checkout._setLoadingSections(['review']);
                    this.save();
                }.bind(this));
        },
        save: function(){
            var params = '';
            if (this._validate()) {
                if (this._config.relations) {
                    if (params) params += '&';
                    params += 'relations=' + this._config.relations.toString();
                }

                if (this._config.agreements) {
                    if (params) {
                        params += '&';
                    }

                    params += Form.serialize(this._config.agreements);
                }

                var options = {
                    method:     this.requestMethod,
                    onComplete: this.onComplete,
                    onSuccess:  this.onSuccess,
                    onFailure:  this.onFailure,
                    parameters: params
                };

                checkout.queueRequest(this._config.saveUrl, options, this._config);
            }
        }
        // TODO: _validate() should be implemented here to validate all sections
    });



    var SectionValidation       = Class.create();
    SectionValidation.prototype = new Validation; // TODO: Looks like this is run in each section template aswell.
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
            // TODO: Do not fail silently
        }

        if (!result && this.options.focusOnError) {
            try{
                Form.getElements(this.form).findAll(function(elm) {
                    return $(elm).hasClassName('validation-failed')
                }).first().focus()
            } catch(e) {
                // TODO: Do not fail silently
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
                throw(e);
            }
        }
    });
})();