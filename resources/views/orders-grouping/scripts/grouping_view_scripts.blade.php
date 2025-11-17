{{-- resources/views/orders-grouping/scripts/grouping_view_scripts.blade.php --}}
 
<script>
            document.addEventListener('DOMContentLoaded', function() {
                const groupSelectors = document.querySelectorAll('.group-selector');
                const saveButton = document.getElementById('save-groups-btn');
                const selectAllBtn = document.getElementById('select-all-btn');
                const clearAllBtn = document.getElementById('clear-all-btn');
                
                // Функция для обновления состояния кнопки "Сохранить"
                function updateSaveButton() {
                    let anyGroupSelected = false;
                    groupSelectors.forEach(selector => {
                        if (selector.checked) {
                            anyGroupSelected = true;
                        }
                    });
                    saveButton.disabled = !anyGroupSelected;
                }
                
                // Обработчик для чекбоксов групп
                groupSelectors.forEach(selector => {
                    selector.addEventListener('change', function() {
                        const card = this.closest('.potential-group-card');
                        const orderCheckboxes = card.querySelectorAll('.order-checkbox');

                        orderCheckboxes.forEach(cb => {
                            cb.disabled = !this.checked;
                            if (!this.checked) {
                                cb.checked = false;
                            }
                        });

                        updateSaveButton();
                    });
                });
                
                // Обработчик "Выбрать все"
                selectAllBtn.addEventListener('click', function() {
                    groupSelectors.forEach(selector => {
                        if (!selector.checked) { // Только если не отмечен
                            selector.checked = true;
                            const card = selector.closest('.potential-group-card');
                            const orderCheckboxes = card.querySelectorAll('.order-checkbox');
                            orderCheckboxes.forEach(cb => {
                                cb.disabled = false;
                                cb.checked = true; // Отмечаем чекбоксы заказов
                            });
                        }
                    });
                    updateSaveButton();
                });

                // Обработчик "Очистить все"
                clearAllBtn.addEventListener('click', function() {
                    groupSelectors.forEach(selector => {
                        if (selector.checked) { // Только если отмечен
                            selector.checked = false;
                            const card = selector.closest('.potential-group-card');
                            const orderCheckboxes = card.querySelectorAll('.order-checkbox');
                            orderCheckboxes.forEach(cb => {
                                cb.disabled = true;
                                cb.checked = false;
                            });
                        }
                    });
                    updateSaveButton();
                });
                 // Инициализация состояния кнопки "Сохранить"
                updateSaveButton();
            });
        </script>