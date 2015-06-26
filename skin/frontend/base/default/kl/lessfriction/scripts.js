document.observe('dom:loaded', function() {
    if ($('login-email')) {
        $('login-email').observe('keypress', bindLoginPost);
    }

    if ($('login-password')) {
        $('login-password').observe('keypress', bindLoginPost);
    }

    $$('.setCheckoutMethod').invoke('observe', 'click', function(evt) {
        checkout.setMethod(this.value);
    });

    if ($('shipping:email')) {
        $('shipping:email').observe('change', function() {
            shippingAddress.customerEmailExists(this.value);
        });
    }

    if ($('billing:email')) {
        $('billing:email').observe('change', function() {
            billingAddress.customerEmailExists(this.value);
        });
    }

});

function bindLoginPost(evt){
    if (evt.keyCode == Event.KEY_RETURN) {
        loginForm.submit();
    }
}

function onepageLogin(button)
{
    if (loginForm.validator && loginForm.validator.validate()) {
        button.disabled = true;
        loginForm.submit();
    }
}
