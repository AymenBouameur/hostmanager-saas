function buildAjaxPostRequest(formName, url, onSuccess) {
    $(document).on('submit', 'form[name=' + formName + ']', function (e) {
        e.preventDefault();
        var data = $('form[name=' + formName + ']').serializeArray();
        var ajax = function () {
            $.ajax({
                method: 'post',
                url: url,
                data: $.param(data),
                dataType: "json",
                beforeSend: function () {
                    $('#ajax-loader').show();
                },
                success: onSuccess,
                complete: function () {
                    $('#ajax-loader').hide();
                }
            });
        };
        if (RECAPTCHA_SITE_KEY !== '') {
            grecaptcha.ready(function () {
                grecaptcha
                    .execute(RECAPTCHA_SITE_KEY, {action: formName})
                    .then(function (token) {
                        data.push({name: 'recaptchaAction', value: formName});
                        data.push({name: 'recaptchaToken', value: token});
                    })
                    .then(ajax);
            });
        } else {
            ajax();
        }
    })
}