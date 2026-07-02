    // js/search.js

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const difficultyFilter = document.getElementById('difficultyFilter');
    const coursesGrid = document.getElementById('coursesGrid');

    let debounceTimeout = null;


    // Trigger update on keyup/change
    if (searchInput) searchInput.addEventListener('input', () => debounce(updateCatalog, 300));
    if (categoryFilter) categoryFilter.addEventListener('change', updateCatalog);
    if (difficultyFilter) difficultyFilter.addEventListener('change', updateCatalog);

    /**
     * Debounce helper to avoid hitting API on every keystroke
     */
    function debounce(func, delay) {

        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(func, delay);
        
    }

    /**
     * Fetch filtered catalog list and update the DOM
     */
    function updateCatalog() {
        const query = encodeURIComponent(searchInput.value.trim());
        const cat = encodeURIComponent(categoryFilter.value);
        const diff = encodeURIComponent(difficultyFilter.value);

        // Visual loading feedback
        coursesGrid.style.opacity = '0.5';

        // Perform AJAX Fetch call
        fetch(`api/search-courses.php?search=${query}&category=${cat}&difficulty=${diff}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(htmlContent => {
                coursesGrid.innerHTML = htmlContent;
                coursesGrid.style.opacity = '1';
            })
            .catch(error => {
                console.error('Error fetching course catalog:', error);
                coursesGrid.innerHTML = `
                    <div style="grid-column: span 3; text-align: center; color: var(--danger); padding: 48px;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 48px; margin-bottom: 16px;"></i>
                        <p>Something went wrong while loading courses. Please reload the page.</p>
                    </div>
                `;
                coursesGrid.style.opacity = '1';
            });
    }
});
