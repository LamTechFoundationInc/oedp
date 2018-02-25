/**
 * @file
 * Select-All Button functionality.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.views_bulk_operations = {
    attach: function (context, settings) {
      $('.vbo-select-all').closest('.view-content').once('select-all').each(Drupal.viewsBulkOperationsFrontUi);
    }
  };

  /**
   * Views Bulk Operation selection object.
   */
  Drupal.viewsBulkOperationsSelection = {
    view_id: '',
    display_id: '',
    list: {},
    $placeholder: null,

    /**
     * Bind event handlers to an element.
     *
     * @param {jQuery} element
     */
    bindEventHandlers: function ($element) {
      var selectionObject = this;
      $element.on('keypress', function (event) {
        // Emulate click action for enter key.
        if (event.which === 13) {
          event.preventDefault();
          event.stopPropagation();
          selectionObject.update(this.checked, $(this).val());
          $(this).trigger('click');
        }
        if (event.which === 32) {
          selectionObject.update(this.checked, $(this).val());
        }
      });
      $element.on('mousedown', function (event) {
        // Act only on left button click.
        if (event.which === 1) {
          selectionObject.update(this.checked, $(this).val());
        }
      });
    },

    /**
     * Perform an AJAX request to update selection.
     *
     * @param {bool} state
     * @param {string} value
     */
    update: function (state, value = null) {
      if (this.view_id.length && this.display_id.length) {
        var list = {};
        if (value && value != 'on') {
          list[value] = this.list[value];
        }
        else {
          list = this.list;
        }
        var op = state ? 'remove' : 'add';

        var $placeholder = this.$placeholder;
        $.ajax('/views-bulk-operations/ajax/' + this.view_id + '/' + this.display_id, {
          method: 'POST',
          data: {
            list: list,
            op: op
          },
          success: function (data) {
            var count = parseInt($placeholder.text());
            count += data.change;
            $placeholder.text(count);
          }
        });
      }
    }
  }

  /**
   * Callback used in {@link Drupal.behaviors.views_bulk_operations}.
   */
  Drupal.viewsBulkOperationsFrontUi = function () {
    var $viewContent = $(this);
    var $viewsTable = $('table.views-table', $viewContent);
    var colspan = $('table.views-table > thead th', $viewContent).length;
    var $primarySelectAll = $('.vbo-select-all', $viewContent);
    var $tableSelectAll = $(this).find('.select-all input').first();
    $primarySelectAll.parent().hide();

    // Add AJAX functionality to table checkboxes.
    var $multiSelectElement = $viewContent.find('.vbo-multipage-selector').first();
    if ($multiSelectElement.length) {

      Drupal.viewsBulkOperationsSelection.$placeholder = $multiSelectElement.find('.placeholder').first();

      // Get the list of all checkbox values and add AJAX callback.
      Drupal.viewsBulkOperationsSelection.list = {};
      $viewsTable.find('tbody .views-field-views-bulk-operations-bulk-form input[type="checkbox"]').each(function () {
        Drupal.viewsBulkOperationsSelection.list[$(this).val()] = $(this).parent().find('label').first().text();
        Drupal.viewsBulkOperationsSelection.bindEventHandlers($(this));
      });
      Drupal.viewsBulkOperationsSelection.view_id = $multiSelectElement.attr('data-view-id');
      Drupal.viewsBulkOperationsSelection.display_id = $multiSelectElement.attr('data-display-id');

      // Bind event handlers to select all checkbox.
      Drupal.viewsBulkOperationsSelection.bindEventHandlers($tableSelectAll);
    }

    var strings = {
      selectAll: $('label', $primarySelectAll.parent()).html(),
      selectRegular: Drupal.t('Select only items on this page')
    };

    // Initialize all selector.
    var $allSelector;
    $allSelector = $('<tr class="views-table-row-vbo-select-all even" style="display: none"><td colspan="' + colspan + '"><div><input type="submit" class="form-submit" value="' + strings.selectAll + '"></div></td></tr>');
    $('tbody', $viewsTable).prepend($allSelector);

    if ($primarySelectAll.is(':checked')) {
      $('input', $allSelector).val(strings.selectRegular);
      $allSelector.show();
    }
    else if ($tableSelectAll.is(':checked')) {
      $allSelector.show();
    }

    $('input', $allSelector).on('click', function (event) {
      event.preventDefault();
      if ($primarySelectAll.is(':checked')) {
        $primarySelectAll.prop('checked', false);
        $allSelector.removeClass('all-selected');
        $(this).val(strings.selectAll);
      }
      else {
        $primarySelectAll.prop('checked', true);
        $allSelector.addClass('all-selected');
        $(this).val(strings.selectRegular);
      }
    });

    $tableSelectAll.on('change', function (event) {
      if (this.checked) {
        $allSelector.show();
      }
      else {
        $allSelector.hide();
        if ($primarySelectAll.is(':checked')) {
          $('input', $allSelector).trigger('click');
        }
      }

    });
  };

})(jQuery, Drupal);
