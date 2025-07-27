    </main>
    
    <!-- Footer -->
    <footer class="bg-white mt-auto">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.
            </p>
        </div>
    </footer>
    
    <!-- Mobile menu toggle script -->
    <script>
        document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
    
    <?php if (isset($extra_scripts)) echo $extra_scripts; ?>
</body>
</html>