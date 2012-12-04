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

            var validator = form.validate({
                rules: rules,
                submitHandler: function() {
                    var skip = form.find("#skip_client_validation").is(":checked");
                    if (skip || validator.form()) {
                        // Disable the submit button while posting.
                        var submit = form.find(":submit").attr("disabled", "disabled");
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
                    }
                    return false;
                }
            });
        });

});