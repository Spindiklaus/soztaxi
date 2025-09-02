<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Переключение между вкладками
        const tabLinks = document.querySelectorAll('.tab-link');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetTab = this.getAttribute('data-tab');
                
                // Скрыть все вкладки
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                    content.classList.remove('active');
                });
                
                // Показать целевую вкладку
                const targetContent = document.getElementById(targetTab);
                if (targetContent) {
                    targetContent.classList.remove('hidden');
                    targetContent.classList.add('active');
                }
                
                // Обновить активные ссылки
                tabLinks.forEach(tabLink => {
                    tabLink.classList.remove('border-blue-500', 'text-blue-600');
                    tabLink.classList.add('border-transparent', 'text-gray-500');
                });
                
                this.classList.remove('border-transparent', 'text-gray-500');
                this.classList.add('border-blue-500', 'text-blue-600');
            });
        });
    });
</script>