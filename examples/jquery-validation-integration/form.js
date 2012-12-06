jQuery(document).ready(function($) {

    $.validator.addMethod("regex", function(value, element, param) {
        return this.optional(element) || new RegExp(param).test(value);
    }, "This field does not conform to a pattern.");

    var form = $("form").on("submit", function() {
        // While rules are loading, make sure form is not submittable.
        return false;
    });

    $.getJSON("rules.json", function(rules) {
        // Remove previous submit handler that blocked submission.
        form.off("submit");

        var submitHandler = function() {
            // Disable the submit button while posting.
            var submit = form.find(":submit").attr("disabled", "disabled");

            // Submit the data to the server.
            $.post(form.attr("action"), form.serialize(), "json")
                .done(function(response) {
                    if (response.successful) {
                        alert("Form submission was successful.")
                    } else {
                        alert("Form submission has failed with " + response.invalid_field_names.length + " invalid field(s).")
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

        // Create the Validator.
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