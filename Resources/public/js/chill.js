/* jslint vars: true */
/*jslint indent: 4 */
/* global moment, $, window  */
'use strict';

var chill = function() {

    /* intialiase the pikaday module */
    function initPikaday(locale) {
        var i18n_trad = {
            fr: {
                previousMonth : 'Mois précédent',
                nextMonth     : 'Mois suivant',
                months        : ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
                weekdays      : ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
                weekdaysShort : ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam']
            },
            nl: {
                previousMonth : 'Vorig maand',
                nextMonth     : 'Volgende maand',
                months        : ['Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','September','Oktober','November','December'],
                weekdays      : ['Zondag','Maandag','Dinsdag','Woensdag','Donderdag','Vrijdag','Zaterdag'],
                weekdaysShort : ['Zon','Ma','Di','Wo','Do','Vri','Zat']
            }
        };

        var pikaday_options = {
            format: 'D-M-YYYY',
            yearRange: [parseInt(moment().format('YYYY')) - 100, parseInt(moment().format('YYYY'))],
        };

        if(locale in i18n_trad) {
            pikaday_options.i18n = i18n_trad[locale];
        }

        $('.datepicker').pikaday(
            pikaday_options
        );
    }

    /* emulate the position:sticky */
    function emulateSticky() {
        var need_emulation = false;

        $('.sticky-form-buttons').each(function(i,stick_element) {
            if($(stick_element).css('position') !== 'sticky') {
                need_emulation = true;
                stick_element.init_offset_top = $(stick_element).offset().top;
            }
        });

        function emulate() {
            $('.sticky-form-buttons').each(function(i,stick_element) {
                if (($(window).scrollTop() + $(window).height()) < stick_element.init_offset_top) {
                    //sticky at bottom
                    $(stick_element).css('position','fixed');
                    $(stick_element).css('bottom','0');
                    $(stick_element).css('top','');
                    $(stick_element).css('width',$(stick_element).parent().outerWidth());
                } else if (stick_element.init_offset_top < $(window).scrollTop()) {
                    //sticky at top
                    $(stick_element).css('position','fixed');
                    $(stick_element).css('top','0');
                    $(stick_element).css('bottom','');
                    $(stick_element).css('width',$(stick_element).parent().outerWidth());
                } else {
                    //no sticky
                    $(stick_element).css('position','initial');
                    $(stick_element).css('bottom','');
                    $(stick_element).css('width','');
                    $(stick_element).css('top','');
                }
            });
        }

        if(need_emulation) {
            $(window).scroll(function() {
                emulate();
            });

            emulate();
        }
    }


    /**
    * Display an alert message when the user wants to leave a page containing a given form
    * in a given state.
    *
    * The action of displaying the form be parametrised as :
    * - always display the alert message when leaving
    * - only display the alert message when the form contains some modified fields.
    *
    * @param{string} form_id An identification string of the form
    * @param{string} alert_message The alert message to display
    * @param{boolean} check_unsaved_data If true display the alert message only when the form 
    * contains some modified fields otherwise always display the alert when leaving
    * @return nothing
    */
    function _generalDisplayAlertWhenLeavingForm(form_id, alert_message, check_unsaved_data) {
        var form_submitted = false;
        var unsaved_data = false;

        $(form_id)
            .submit(function() {
                form_submitted = true;
            })
            .on('reset', function() {
                unsaved_data = false;
            })
        ;

        $.each($(form_id).find(':input'), function(i,e) {
            $(e).change(function() {
                unsaved_data = true;
            });
        });

        $(window).bind('beforeunload', function(){
            if((!form_submitted) && (unsaved_data || !check_unsaved_data)) {
                return alert_message;
            }
        });
    }

    /** 
    * Mark the choices "not specified" as check by default. 
    * 
    * This function apply to `custom field choices` when the `required` 
    * option is false and `expanded` is true (checkboxes or radio buttons).
    * 
    * @param{string} choice_name the name of the input
    */
    function checkNullValuesInChoices(choice_name) {
        var choices;
        choices = $("input[name='"+choice_name+"']:checked");
        if (choices.size() === 0) {
            $.each($("input[name='"+choice_name+"']"), function (i, e) {
                if (e.value === "") {
                    e.checked = true;
                }
            });
        }
    }

    /**
    * Display an alert message when the user wants to leave a page containing a given
    * modified form.
    *
    * @param{string} form_id An identification string of the form
    * @param{string} alert_message The alert message to display
    * @return nothing
    */
    function displayAlertWhenLeavingModifiedForm(form_id, alert_message) {
        _generalDisplayAlertWhenLeavingForm(form_id, alert_message, true);
    }

    /**
    * Display an alert message when the user wants to leave a page containing a given
    * form that was not submitted.
    *
    * @param{string} form_id An identification string of the form
    * @param{string} alert_message The alert message to display
    * @return nothing
    */
    function displayAlertWhenLeavingUnsubmittedForm(form_id, alert_message) {
        _generalDisplayAlertWhenLeavingForm(form_id, alert_message, false);
    }

    /* Enable the following behavior : when the user change the value
    of an other field, its checkbox is checked.
    */
    function checkOtherValueOnChange() {
        $('.input-text-other-value').each(function() {
            $(this).change(function() {
                var checkbox = $(this).parent().find('input[type=checkbox][value=_other]')[0];
                $(checkbox).prop('checked', ($(this).val() !== ''));
            });
        });
    }

    /**
    * Create an interraction between two select element (the parent and the
    * child) of a given form : each parent option has a category, the
    * child select only display options that have the same category of the
    * parent optionn
    * 
    * The parent must have the class "chill-category-link-parent". 
    * 
    * The children must have the class "chill-category-link-child". Each option
    * of the parent must have the attribute `data-link-category`, with the value of
    * the connected option in parent.
    * 
    * Example :
    * 
    * ```html
    * <select name="country" class="chill-category-link-parent">
    *   <option value="BE">Belgium</option>
    *   <option value="FR">France</option>
    * </select>
    * 
    * <select name="cities">class="chill-category-link-children">
    *   <option value="paris" data-link-category="FR">Paris</option>
    *   <option value="toulouse" data-link-category="FR">Toulouse</option>
    *   <option value="bruxelles" data-link-category="BE">Bruxelles</option>
    *   <option value="liege" data-link-category="BE">Liège</option>
    *   <option value="mons" data-link-category="BE">Mons</option>
    * </select>
    * ```
    * 
    * TODO ECRIRE LA DOC METTRE LES TESTS DANS git :
    * tester que init est ok :
       - quand vide
       - quand choix
    * tester que quand sélection
       - quand vide
       - quand choix
    */
    function categoryLinkParentChildSelect() {
        var forms_to_link = $('form:has(select.chill-category-link-parent)');

        forms_to_link.each(function(i,form_selector) {
            var form = $(form_selector), parent_multiple;
            form.old_category = null;
            form.link_parent =  $(form).find('.chill-category-link-parent');
            form.link_child = $(form).find('.chill-category-link-child');
            
            // check if the parent allow multiple or single results
            parent_multiple = $(form).find('.chill-category-link-parent').get(0).multiple;
            // if we use select2, parent_multiple will be `undefined`
            if (typeof parent_multiple == 'undefined') {
                // currently, I do not know how to check if multiple using select2.
                // we suppose that multiple is false (old behaviour)
                parent_multiple = false
            }
            
            $(form.link_parent).addClass('select2');
            $(form.link_parant).select2({allowClear: true}); // it is weird: when I fix the typo here, the whole stuff does not work anymore...
  
            if (parent_multiple == false) {

                form.old_category = null;
                if($(form.link_parent).select2('data') !== null) {
                    form.old_category = ($(form.link_parent).select2('data').element[0].dataset.linkCategory);
                }

                $(form.link_child).find('option')
                    .each(function(i,e) {
                        if(
                            ((!$(e).data('link-category')) || $(e).data('link-category') == form.old_category) &&
                            ((!$(e).data('link-categories')) || form.old_category in $(e).data('link-categories').split(','))
                        ) {
                            $(e).show();
                            // here, we should handle the optgroup
                        } else {
                            $(e).hide();
                            // here, we should handle the optgroup
                        }
                    });

                form.link_parent.change(function() {
                    var new_category = ($(form.link_parent).select2('data').element[0].dataset.linkCategory);
                    if(new_category != form.old_category) {
                        $(form.link_child).find('option')
                            .each(function(i,e) {
                                if(
                                    ((!$(e).data('link-category')) || $(e).data('link-category') == new_category) &&
                                    ((!$(e).data('link-categories')) || new_category in $(e).data('link-categories').split(','))
                                ) {
                                    $(e).show();
                                    // here, we should handle the optgroup
                                } else {
                                    $(e).hide();
                                    // here, we should handle the opgroup
                                }
                            });
                        $(form.link_child).find('option')[0].selected = true;
                        form.old_category = new_category;
                    }
                });
            } else {
                var i=0, 
                    selected_items = $(form.link_parent).find(':selected');
                
                form.old_categories = [];
                for (i=0;i < selected_items.length; i++) {
                    form.old_categories.push(selected_items[i].value);
                }

                $(form.link_child).find('option')
                    .each(function(i,e) {
                        var visible;
                        if(form.old_categories.indexOf(e.dataset.linkCategory) != -1) {
                            $(e).show();
                            if (e.parentNode instanceof HTMLOptGroupElement) {
                                $(e.parentNode).show();
                            }
                        } else {
                            $(e).hide();
                            if (e.parentNode instanceof HTMLOptGroupElement) {
                                // we check if the other options are visible.
                                visible = false
                                for (var l=0; l < e.parentNode.children.length; l++) {
                                    if (window.getComputedStyle(e.parentNode.children[l]).getPropertyValue('display') != 'none') {
                                        visible = true;
                                    }
                                }
                                // if any options are visible, we hide the optgroup
                                if (visible == false) {
                                    $(e.parentNode).hide();
                                }
                            }
                        }
                    });

                form.link_parent.change(function() {
                    var new_categories = [], 
                        selected_items = $(form.link_parent).find(':selected'),
                        visible;
                    for (i=0;i < selected_items.length; i++) {
                        new_categories.push(selected_items[i].value);
                    }
                    
                    if(new_categories != form.old_categories) {
                        $(form.link_child).find('option')
                            .each(function(i,e) {
                                if(new_categories.indexOf(e.dataset.linkCategory) != -1) {
                                    $(e).show();
                                    // if parent is an opgroup, we show it
                                    if (e.parentNode instanceof HTMLOptGroupElement) {
                                        $(e.parentNode).show();
                                    }
                                } else {
                                    $(e).hide();
                                    // we check if the parent is an optgroup
                                    if (e.parentNode instanceof HTMLOptGroupElement) {
                                        // we check if other options are visible
                                        visible = false
                                        for (var l=0; l < e.parentNode.children.length; l++) {
                                            if (window.getComputedStyle(e.parentNode.children[l]).getPropertyValue('display') != 'none') {
                                                visible = true;
                                            }
                                        }
                                        // if any options are visible, we hide the optgroup
                                        if (visible == false) {
                                            $(e.parentNode).hide();
                                }
                                    }
                                }
                            });
                        //$(form.link_child).find('option')[0].selected = true;
                        form.old_categories = new_categories;
                    }
                });
            }  
        });
    }

    return {
        initPikaday: initPikaday,
        emulateSticky: emulateSticky,
        checkOtherValueOnChange: checkOtherValueOnChange,
        displayAlertWhenLeavingModifiedForm: displayAlertWhenLeavingModifiedForm,
        displayAlertWhenLeavingUnsubmittedForm: displayAlertWhenLeavingUnsubmittedForm,
        checkNullValuesInChoices: checkNullValuesInChoices,
        categoryLinkParentChildSelect: categoryLinkParentChildSelect,
    };
} ();
