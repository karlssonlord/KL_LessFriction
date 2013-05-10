var loginForm;

document.observe('dom:loaded', function() {
    loginForm = new VarienForm('login-form', true);

    if ($('login-email')) {
        $('login-email').observe('keypress', bindLoginPost);
    }

    if ($('login-password')) {
        $('login-password').observe('keypress', bindLoginPost);
    }

    $$('.setCheckoutMethod').invoke('observe', 'click', function(evt) {
        checkout.setMethod(this.value);
    });

    $$('.btn-checkout').invoke('observe', 'click', function(evt) {
        Event.stop(evt);
        review.save();
    });
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
