jQuery(document).ready(function($) {

    $.validator.addMethod("alphanumeric", function(value, element) {
        return this.optional(element) || /^[a-z0-9]+$/i.test(value);
    }, "This field must contain only letters and numbers.");

    var form = $("form").on("submit", function() {
        // While rules are loading, make sure form is not submittable.
        return false;
    });

    $.get("rules.json", {}, "json")
        .done(function(rules) {
            // Remove previous submit handler that blocked submission.
            form.off("submit");

            var submitHandler = function() {
                // Disable the submit button while posting.
                var submit = form.find(":submit").attr("disabled", "disabled");

                // Submit the data to the server.
                $.post(form.attr("action") || "", form.serialize(), "json")
                    .done(function(response) {
                        if (response.successful) {
                            alert("Form submission was successful.")
                        } else {
                            alert("Form submission has failed.")
                        }
                    })
                    .fail(function() {
                        alert("Failed to communicate with server.");
                    })
                    .always(function() {
                        // Enable the submit button after posting.
                        submit.removeAttr("disabled");
                    });
            };

            form.validate({
                rules: rules,
                submitHandler: submitHandler,
                invalidHandler: function() {
                    if (form.find("#skip_client_validation").is(":checked")) {
                        submitHandler();
                    }
                }
            });
        });

});