/* jslint vars: true */
/*jslint indent: 3 */
/* global moment, $  */
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
   * Protect to leave a page where the form  'form_id' has unsaved changes by displaying
   * a popup-containing a warning.
   *
   * @param{string} form_id A identification string of the form
   * @param{string} unsaved_data_message The string to display in the warning pop-up
   * @return nothing
   */
   function protectUnsavedDataForFrom(form_id, unsaved_data_message) {
      var form_submitted = false;
      var unsaved_data = false;

      $(form_id)
         .submit(function() {
            form_submitted = true;
         })
         .on('reset', function() {
            unsaved_data = false;
         });



      $.each($(form_id).find(':input'), function(i,e) {
         $(e).change(function() {
            unsaved_data = true;
         });
      });

      $(window).bind('beforeunload', function(){
         if((!form_submitted) && unsaved_data) {
            return unsaved_data_message;
         }
      });
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

   return {
      initPikaday: initPikaday,
      emulateSticky: emulateSticky,
      checkOtherValueOnChange: checkOtherValueOnChange,
      protectUnsavedDataForFrom: protectUnsavedDataForFrom,
   };
} ();