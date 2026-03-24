(function ($) {
  "use strict";

  const QuizBuilder = {
    init: function () {
      this.cacheElements();
      this.bindEvents();
      this.initSortable();
    },

    cacheElements: function () {
      this.$modal = $("#question-type-modal");
      this.$questionsList = $("#sortable-questions");
      this.$instructionsContainer = $("#instructions-container");
      this.$libraryItems = $(".library-question-item");
      this.$tabButtons = $(".tab-button");
      this.$saveButton = $("#save-quiz");
    },

    bindEvents: function () {
      // Add question button click
      $(".add-question-btn").on("click", () => this.openModal());

      // Question type selection
      $(".question-type-card").on("click", (e) => this.selectQuestionType(e));

      // Modal close
      $(".modal-close, .modal-overlay").on("click", () => this.closeModal());

      // Add question to test
      $(document).on("click", ".add-to-test", (e) => this.addToTest(e));

      // Remove question from test
      $(document).on("click", ".remove-question", (e) =>
        this.removeQuestion(e),
      );

      // Remove all questions
      $("#remove-all-questions").on("click", () => this.removeAllQuestions());

      // Add instruction
      $("#add-instruction").on("click", () => this.addInstruction());

      // Remove instruction
      $(document).on("click", ".remove-instruction", (e) =>
        this.removeInstruction(e),
      );

      // Tab switching
      this.$tabButtons.on("click", (e) => this.switchTab(e));

      // Save quiz
      this.$saveButton.on("click", () => this.saveQuiz());

      // Delete quiz
      $("#delete-quiz").on("click", () => this.deleteQuiz());

      // Search questions
      $("#question-search").on("keyup", (e) => this.searchQuestions(e));

      // Filter by type
      $("#question-type-filter").on("change", (e) => this.filterQuestions(e));

      // Library item click (for adding)
      $(".library-question-item").on("click", function (e) {
        if (!$(e.target).closest(".add-to-test").length) {
          $(this).find(".add-to-test").trigger("click");
        }
      });
    },

    initSortable: function () {
      if (this.$questionsList.length) {
        this.$questionsList.sortable({
          handle: ".drag-handle",
          placeholder: "sortable-placeholder",
          update: () => this.updateQuestionNumbers(),
        });
      }
    },

    openModal: function () {
      this.$modal.fadeIn(200);
    },

    closeModal: function () {
      this.$modal.fadeOut(200);
    },

    selectQuestionType: function (e) {
      const type = $(e.currentTarget).data("type");
      this.closeModal();

      // Redirect to question creation page
      window.location.href = `admin.php?page=wpsqp-add-question&type=${type}&test_id=${wpsqp_quiz.test_id}`;
    },

    addToTest: function (e) {
      e.stopPropagation();
      const $btn = $(e.currentTarget);
      const $item = $btn.closest(".library-question-item");
      const questionId = $item.data("id");
      const questionType = $item.data("type");
      const questionText = $item.find(".question-text").text();

      // Check if already added
      if ($(`.selected-question-item[data-id="${questionId}"]`).length) {
        alert("This question is already added to the test.");
        return;
      }

      const questionCount =
        this.$questionsList.find(".selected-question-item").length + 1;

      const html = `
                <div class="selected-question-item" data-id="${questionId}">
                    <span class="drag-handle dashicons dashicons-menu"></span>
                    <span class="question-number">${questionCount}.</span>
                    <div class="question-content">
                        <span class="question-type">${questionType.replace(/_/g, " ")}</span>
                        <span class="question-text">${questionText}</span>
                    </div>
                    <input type="hidden" name="questions[]" value="${questionId}">
                    <button type="button" class="button button-small remove-question">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
            `;

      this.$questionsList.append(html);
      this.$questionsList.find(".no-questions-message").remove();
      this.updateQuestionNumbers();
    },

    removeQuestion: function (e) {
      e.stopPropagation();
      if (!confirm(wpsqp_quiz.strings.confirm_delete)) return;

      $(e.currentTarget).closest(".selected-question-item").remove();

      if (!this.$questionsList.find(".selected-question-item").length) {
        this.$questionsList.html(`
                    <div class="no-questions-message">
                        <p>No questions selected. Drag questions from the library or click "Add New" to create one.</p>
                    </div>
                `);
      }

      this.updateQuestionNumbers();
    },

    removeAllQuestions: function () {
      if (!confirm("Are you sure you want to remove all questions?")) return;

      this.$questionsList.html(`
                <div class="no-questions-message">
                    <p>No questions selected. Drag questions from the library or click "Add New" to create one.</p>
                </div>
            `);
    },

    updateQuestionNumbers: function () {
      this.$questionsList
        .find(".selected-question-item")
        .each(function (index) {
          $(this)
            .find(".question-number")
            .text(index + 1 + ".");
          $(this).find('input[name="questions[]"]').val($(this).data("id"));
        });
    },

    addInstruction: function () {
      const index =
        this.$instructionsContainer.find(".instruction-item").length;

      const html = `
                <div class="instruction-item" data-index="${index}">
                    <div class="instruction-header">
                        <span class="instruction-title">Page ${index + 1}</span>
                        <button type="button" class="button button-small remove-instruction">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    <div class="instruction-content">
                        <input type="text" name="instructions[${index}][title]" 
                               placeholder="Page Title">
                        <textarea name="instructions[${index}][content]" 
                                  rows="4" 
                                  placeholder="Instruction content..."></textarea>
                    </div>
                </div>
            `;

      this.$instructionsContainer.append(html);
      this.$instructionsContainer.find(".no-instructions-message").remove();
    },

    removeInstruction: function (e) {
      if (!confirm("Are you sure you want to remove this instruction page?"))
        return;

      $(e.currentTarget).closest(".instruction-item").remove();

      if (!this.$instructionsContainer.find(".instruction-item").length) {
        this.$instructionsContainer.html(`
                    <div class="no-instructions-message">
                        <p>No instructions added yet. Click "Add Instruction Page" to create one.</p>
                    </div>
                `);
      }
    },

    switchTab: function (e) {
      const $btn = $(e.currentTarget);
      const tabId = $btn.data("tab");

      this.$tabButtons.removeClass("active");
      $btn.addClass("active");

      $(".tab-panel").removeClass("active");
      $(`#tab-${tabId}`).addClass("active");
    },

    saveQuiz: function () {
      const formData = new FormData(document.getElementById("wpsqp-quiz-form"));
      formData.append("action", "wpsqp_save_test");
      formData.append("nonce", wpsqp_quiz.nonce);
      formData.append("test_id", wpsqp_quiz.test_id);

      // Show loading
      this.$saveButton.prop("disabled", true).text("Saving...");

      $.ajax({
        url: wpsqp_quiz.ajax_url,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          if (response.success) {
            alert(response.data.message);
            if (!wpsqp_quiz.test_id && response.data.test_id) {
              window.location.href = `admin.php?page=wpsqp-make-quiz&test_id=${response.data.test_id}`;
            }
          } else {
            alert("Error saving quiz. Please try again.");
          }
        },
        error: function () {
          alert("Error saving quiz. Please try again.");
        },
        complete: function () {
          $("#save-quiz").prop("disabled", false).text("Save Quiz");
        },
      });
    },

    deleteQuiz: function () {
      if (
        !confirm(
          "Are you sure you want to delete this quiz? This action cannot be undone.",
        )
      )
        return;

      // Add delete logic here
    },

    searchQuestions: function (e) {
      const searchTerm = e.target.value.toLowerCase();

      this.$libraryItems.each(function () {
        const text = $(this).find(".question-text").text().toLowerCase();
        if (text.includes(searchTerm)) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    },

    filterQuestions: function (e) {
      const type = e.target.value;

      this.$libraryItems.each(function () {
        if (!type || $(this).data("type") === type) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    },
  };

  $(document).ready(() => QuizBuilder.init());
})(jQuery);
