jQuery(document).ready(function($) {

    var form = $("form").on("submit", function() {
        // While rules are loading, make sure form is not submittable.
        return false;
    });

    $.get("rules.json", {}, "json")
        .done(function(rules) {
            var validator = form.validate({ rules: rules });

            // Remove previous submit handler that blocked submission.
            form.off("submit");

            form.on("submit", function() {
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
            });
        });

});