document.addEventListener('click', function (event) {
    var trigger = event.target.closest('[data-confirm]');
    if (trigger && !window.confirm(trigger.getAttribute('data-confirm'))) {
        event.preventDefault();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    var templateSelect = document.querySelector('[data-task-template]');
    var preview = document.querySelector('[data-suggestion-preview]');
    if (templateSelect && preview) {
        var titleInput = document.getElementById('task-title');
        var descriptionInput = document.getElementById('task-description');
        var prioritySelect = document.getElementById('task-priority');
        var checklist = preview.querySelector('[data-suggestion-list]');

        function renderSuggestion(option, applyValues) {
            checklist.replaceChildren();
            preview.hidden = !option.value;
            if (!option.value) {
                return;
            }

            if (applyValues) {
                titleInput.value = option.dataset.title || '';
                descriptionInput.value = option.dataset.description || '';
                prioritySelect.value = option.dataset.priority || 'normal';
            }

            var items = JSON.parse(option.dataset.checklist || '[]');
            items.forEach(function (item) {
                var listItem = document.createElement('li');
                listItem.textContent = item;
                checklist.appendChild(listItem);
            });
        }

        templateSelect.addEventListener('change', function () {
            renderSuggestion(templateSelect.options[templateSelect.selectedIndex], true);
        });

        renderSuggestion(templateSelect.options[templateSelect.selectedIndex], false);
    }

    document.querySelectorAll('[data-suggestion-prefill]').forEach(function (suggestionSelect) {
        suggestionSelect.addEventListener('change', function () {
            var option = suggestionSelect.options[suggestionSelect.selectedIndex];
            var title = document.getElementById(suggestionSelect.dataset.titleTarget);
            var description = document.getElementById(suggestionSelect.dataset.descriptionTarget);
            var priority = document.getElementById(suggestionSelect.dataset.priorityTarget);
            if (!option.value) {
                return;
            }
            if (title) {
                title.value = option.dataset.title || '';
            }
            if (description) {
                description.value = option.dataset.description || '';
            }
            if (priority) {
                priority.value = option.dataset.priority || 'normal';
            }
        });
    });

    var shiftPreset = document.querySelector('[data-shift-preset]');
    if (shiftPreset) {
        shiftPreset.addEventListener('change', function () {
            var option = shiftPreset.options[shiftPreset.selectedIndex];
            if (option.value) {
                document.querySelector('input[name="start_time"]').value = option.dataset.start || '';
                document.querySelector('input[name="end_time"]').value = option.dataset.end || '';
            }
        });
    }
});
