/*
 * GotChosen
 */

var GotChosen = {};

GotChosen.ucfirst = function(str)
{
    return str.charAt(0).toUpperCase() + str.substr(1);
};

GotChosen.id = function($el)
{
    if ( $el.attr('id') ) {
        return $el.attr('id');
    }

    var newId = 'gcauto' + Math.floor(Math.random() * 1000000000);
    $el.attr('id', newId);
    return newId;
};

GotChosen.FormExpand = function($el)
{
    this.construct($el);
};

GotChosen.FormExpand.prototype = {
    $el: null,
    $toggle: null,
    $div: null,

    expandHtml: '<i class="icon-resize-full"></i> Expand',
    hideHtml: '<i class="icon-resize-small"></i> Hide',

    construct: function($el) {
        this.$el = $el;
        this.$toggle = $el.find('legend button');
        this.$div = $el.find('div.form-collapse');

        this.$toggle.click($.proxy(this.onToggleClick, this));

        if ( $el.hasClass('Visible') ) {
            this.expand();
        }

        this.expandFieldsWithErrors();
    },

    expand: function() {
        this.$toggle.html(this.hideHtml);
        this.$div.slideDown('fast');
    },

    hide: function() {
        this.$toggle.html(this.expandHtml);
        this.$div.slideUp('fast');
    },

    onToggleClick: function() {
        if ( this.$div.css('display') == 'none' ) {
            this.expand();
        } else {
            this.hide();
        }
    },

    // finds div.control-group.error and auto expands parent div.form-collapse
    expandFieldsWithErrors: function() {
        if ( this.$div.find('div.control-group.error').length > 0
            && this.$div.css('display') == 'none' ) {
            this.expand();
        }
    }
};

GotChosen.CheckDepend = function()
{
    this.construct();
};

GotChosen.CheckDepend.prototype = {
    mappings: [],

    construct: function() {},

    add: function($check, $elements)
    {
        if ( !$check.prop('checked') ) {
            $elements.hide();
        }

        $check.change(function() {
            if ( $(this).prop('checked') ) {
                $elements.show();
            } else {
                $elements.hide();
            }
        });
    }
};

GotChosen.hashToIdName = function(hash)
{
    var idName = [];
    _.each(hash, function(v, k) {
        idName.push({id: k, name: v});
    });
    return idName;
};

$.fn.ovAddOptions = function(optList)
{
    _.each(optList, function(o) {
        this.append($('<option></option>').attr('value', o.id).text(o.name));
    }, this);

    return this;
};

GotChosen.ModalForm = function(sel) {
    this.construct(sel);
};

GotChosen.ModalForm.prototype = {
    construct: function(sel) {
        this.$form = $(sel);
        this.$submit = this.$form.parents('div.modal').find('.modal-footer > button.btn-primary');
        this.$modalTarget = $(this.$form.find('input[name="modal_target"]').val());

        this.setupEvents();
    },

    setupEvents: function() {
        this.$submit.off('click').on('click', _.bind(this.submitForm, this));
    },

    submitForm: function(e) {
        var data = this.$form.serialize();
        $.ajax(this.$form.attr('action'), {
            data: data,
            dataType: 'json',
            type: 'POST',
            success: _.bind(this.submitComplete, this)
        });
    },

    submitComplete: function(json) {
        this.clearErrors();
        if ( json.success ) {
            this.hideModal();
            this.$modalTarget.ovAddOptions([{id: json.record.id, name: json.record.text}]);
            this.$modalTarget.val(json.record.id);
        } else {
            this.renderErrors(json.errors);
        }
    },

    renderErrors: function(errors) {
        _.each(errors, function(message, key) {
            var $group = $('#' + key + '_control_group');
            $group
                .addClass('error')
                .find('div.controls')
                .append($('<span></span>').addClass('help-block rescue-error').html(message));
        });
    },

    clearErrors: function() {
        this.$form.find('div.control-group').removeClass('error');
        this.$form.find('span.help-block.rescue-error').remove();
    },

    hideModal: function() {
        this.$form.parents('div.modal').modal('hide');
    }
};

GotChosen.MassCheck = function($el)
{
    this.construct($el);
};

GotChosen.MassCheck.prototype = {
    construct: function($el) {
        this.$el = $el;
        this.target = $el.data('target');

        if ( $el.hasClass('CheckAll') ) {
            $el.click($.proxy(this.doCheckAll, this));
        } else if ( $el.hasClass('UncheckAll') ) {
            $el.click($.proxy(this.doUncheckAll, this));
        }
    },

    doCheckAll: function() { this.tickAll(true); },
    doUncheckAll: function() { this.tickAll(false); },

    tickAll: function(tick) {
        $('input[type="checkbox"].' + this.target).each(function() {
            if ( !$(this).prop('disabled') ) {
                $(this).prop('checked', tick);
            }
        });
    }
};

GotChosen.ExistsCheck = function($el)
{
    this.construct($el);
};

// maps form ID => [ChosenExistsCheck fields]
GotChosen.ExistsCheck.formRegistry = {};
// maps input ID => original value
GotChosen.ExistsCheck.originalValues = {};

GotChosen.ExistsCheck.prototype = {
    construct: function($el) {
        this.$el = $el;
        this.$form = $el.parents('form');
        this.$submit = $('button[type="submit"]');
        this.originalValue = this.$el.val();
        this.path = $el.data('path');
        this.success = $el.data('success');
        this.error = $el.data('error');
        this.submitting = false;

        var formId = GotChosen.id(this.$form);
        if ( typeof GotChosen.ExistsCheck.formRegistry[formId] == 'undefined' ) {
            this.doSubmit = true;
            GotChosen.ExistsCheck.formRegistry[formId] = this.$form.find('.ChosenExistsCheck');
        } else {
            this.doSubmit = false;
        }

        GotChosen.ExistsCheck.originalValues[GotChosen.id(this.$el)] = this.originalValue;

        this.setupEvents();
    },

    setupEvents: function() {
        this.$el.blur(_.bind(function() {
            var data = {value: this.$el.val()};
            if ( data.value == '' ) {
                return;
            }

            if ( data.value == this.originalValue ) {
                this.handleResponse(false);
                return;
            }

            $.ajax(this.path, {
                data: data,
                dataType: 'json',
                success: _.bind(this.handleResponse, this)
            });
        }, this));

        /*
         * Explanation time! Since this is kinda unclear.
         *
         * This form submit handler is only added on the first ChosenExistsCheck of the form.
         * It goes through all ChosenExistsCheck inputs on the form, and creates an array of Deferred,
         * either an "OK" (same as original value), "FAIL" (value is blank), or an ajax request that
         * will resolve later (which we check for exists = true or false).
         *
         * The Deferreds all run in $.when.apply, and we send the result to handleResponseSubmit
         * after examining the returned data to see if any values exist already.
         */
        if ( this.doSubmit ) {
            this.$form.submit(_.bind(function(e) {
                e.preventDefault();

                // If submitting, then we just return.
                if (this.submitting) return false;
                this.submitting = true;

                var GCE = GotChosen.ExistsCheck;

                var deferreds = [];
                deferreds.push({isOriginal: true});
                // we set a dummy value above so we only care about the multi-deferred response.
                // handling single + multiple argument types with arrays and objects is a bug-ridden pain.

                GCE.formRegistry[GotChosen.id(this.$form)].each(function() {
                    if ( $(this).val() == '' ) {
                        deferreds.push($.Deferred().reject());
                    } else if ( $(this).val() == GCE.originalValues[GotChosen.id($(this))] ) {
                        deferreds.push({ isOriginal: true });
                    } else {
                        deferreds.push($.ajax($(this).data('path'), {
                            data: {value: $(this).val()},
                            dataType: 'json'
                        }));
                    }
                });

                $.when.apply(null, deferreds)
                    .done(_.bind(function() {
                        var ex = _.some(arguments, function(arg) {
                            return $.isArray(arg) ? arg[0].exists : !arg.isOriginal;
                        });

                        this.handleResponseSubmit({exists: ex});
                    }, this))
                    .fail(function() {
                        // something was blank
                    });
            }, this));
        }
    },

    handleResponse: function(json) {
        var $group = this.$el.parents('div.control-group').first();
        var $parent = this.$el.parent();
        $parent.find('span.help-inline').remove();
        $group.removeClass('error success');
        this.$submit.removeAttr('disabled');

        if ( !json ) {
            return;
        }

        if ( json.exists ) {
            $group.addClass('error');
            $parent.append($('<span class="help-inline"></span>').text(this.error));
            this.$submit.attr('disabled', 'disabled');
        } else {
            $group.addClass('success');
            $parent.append($('<span class="help-inline"></span>').text(this.success));
            this.$submit.removeAttr('disabled');
        }
    },

    handleResponseSubmit: function(json) {
        this.handleResponse(json);

        if ( !json.exists ) {
            /*
             * Due to the overly complicated nature of some other code,
             * here's another explanation and a bit of weird code.
             *
             * Upon determination that the form should be submitted,
             * we unbind the original submit funciton, then submit the form.
             * Afterwards, we bind a new submit handler which simply returns
             * false so that any subsequent submits will basically do nothing.
             */
            this.$form.unbind('submit').submit();
            this.$form.submit(_.bind(function(e) {
                e.preventDefault();
                return false;
            }, this));
        }
        else
        {
            this.submitting = false;
        }
    }
};

GotChosen.FakePlaceholder = function($el) {
    this.construct($el);
};

GotChosen.FakePlaceholder.prototype = {
    construct: function($el) {
        this.$el = $el;
        this.placeholder = $el.data('placeholder');
        this.setupEvents();

        $el.val(this.placeholder);
    },

    setupEvents: function() {
        var ph = this.placeholder;
        var $el = this.$el;
        this.$el.focus(function() {
            if ( $el.val() == ph ) {
                $el.val('');
            }
        });

        this.$el.blur(function() {
            if ( $el.val() == '' ) {
                $el.val(ph);
            }
        });
    }
};

$(function() {
    $('.ChosenFormExpand').each(function() {
        new GotChosen.FormExpand($(this));
    });

    $('.CheckAll, .UncheckAll').each(function() {
        new GotChosen.MassCheck($(this));
    });

    $('form.ChosenTTForm').each(function() {
        $(this).find('input[type="text"][title], input[type="password"][title], select[title], input[type="email"][title]')
            .tooltip({placement: 'right'});
    });

    $('.AddTooltip').each(function() {
       $(this).tooltip({placement: $(this).data('placement') || 'right'});
    });

    $('a.disabled').click(function(e) {
        e.preventDefault();
    });

    $('input.autoselect, textarea.autoselect').click(function() {
        this.select();
    });

    $('.ChosenExistsCheck').each(function() {
        new GotChosen.ExistsCheck($(this));
    });

    $('[data-placeholder]').each(function() {
        new GotChosen.FakePlaceholder($(this));
    });

    $('[data-gc-toggle]').each(function() {
        $(this).click(function(e) {
            e.preventDefault();
            // gc-toggle becomes gcToggle in JS per w3c recommendation
            var id = $(this).data('gcToggle');
            $('#' + id).toggle();
        });
    });

    $.fn.maxCharacters = function(appendTo) {
        return this.each(function(){
            $(this).on('keyup', function(){
                var maxLength = $(this).attr('maxlength');
                $(this).val($(this).val().substr(0, maxLength));

                var length = $(this).val().length;

                if (!maxLength) return;

                var leftover = maxLength - length;
                if (leftover < 0) { leftover = 0; }

                $(this).parents(appendTo).find('.max-limit').find('strong').html(leftover);
            });

            var length = $(this).val().length;
            var maxLength = $(this).attr('maxlength');

            if (!maxLength) { maxLength = limit; }

            var leftover = maxLength - length;
            if (leftover < 0) { leftover = 0; }

            $(this).parents(appendTo)
                .append($('<div class="max-limit">You have <strong>' + leftover + '</strong> characters remaining.</div>'));
        });
    }
});
