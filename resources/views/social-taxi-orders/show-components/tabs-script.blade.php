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
    
    // Показываем информацию о клиенте, если есть активные фильтры или по умолчанию
document.addEventListener('DOMContentLoaded', function() {
    // Можно добавить логику для автоматического открытия при определенных условиях
    // Например, если есть важные данные клиента
    const hasImportantClientData = {{ 
        ($order->client_invalid || $order->kl_id || $order->client_tel) ? 'true' : 'false' 
    }};
    
    // Пока оставим закрытым по умолчанию
});

function toggleClientInfo() { // раскрывает данные по клиенту в show
    const content = document.getElementById('client-info-content');
    const arrow = document.getElementById('client-info-arrow');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        arrow.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}

function toggleTripInfo() { // раскрывает данные по поездке в show
    const content = document.getElementById('trip-info-content');
    const arrow = document.getElementById('trip-info-arrow');
    
    content.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}

</script>