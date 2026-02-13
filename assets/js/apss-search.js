/**
 * APSS Search Overlay Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    const triggers = document.querySelectorAll('.apss-trigger');
    const overlay = document.getElementById('apss-search-overlay');
    const closeBtn = document.querySelector('.apss-close');
    const searchInput = document.getElementById('apss-search-input');
    const resultsContainer = document.getElementById('apss-search-results');
    const spinner = document.querySelector('.apss-spinner');
    
    let abortController = null;
    let debounceTimer = null;

    if (!triggers.length || !overlay || !closeBtn || !searchInput) return;

    // Toggle Overlay
    const openOverlay = () => {
        overlay.style.display = 'flex';
        document.body.classList.add('apss-overlay-open');
        setTimeout(() => {
            searchInput.focus();
        }, 100);
    };

    const closeOverlay = () => {
        overlay.style.display = 'none';
        document.body.classList.remove('apss-overlay-open');
        searchInput.value = '';
        resultsContainer.innerHTML = '';
        if (abortController) abortController.abort();
    };

    triggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            openOverlay();
        });
    });

    closeBtn.addEventListener('click', closeOverlay);

    // Close on ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.style.display === 'flex') {
            closeOverlay();
        }
    });

    // Close on background click (optional but nice)
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeOverlay();
        }
    });

    // Search Logic
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.trim();

        clearTimeout(debounceTimer);
        if (abortController) abortController.abort();

        if (term.length < 2) {
            resultsContainer.innerHTML = '';
            spinner.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            performSearch(term);
        }, 300);
    });

    async function performSearch(term) {
        spinner.style.display = 'block';
        resultsContainer.style.opacity = '0.5';

        abortController = new AbortController();

        try {
            const response = await fetch(`${apssSearchData.root}apss/v1/search?term=${encodeURIComponent(term)}`, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': apssSearchData.nonce
                },
                signal: abortController.signal
            });

            if (!response.ok) throw new Error('Search failed');

            const results = await response.json();
            renderResults(results);
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('Search aborted');
            } else {
                console.error('Search error:', error);
                resultsContainer.innerHTML = '<p class="apss-no-results">Something went wrong. Please try again.</p>';
            }
        } finally {
            spinner.style.display = 'none';
            resultsContainer.style.opacity = '1';
        }
    }

    function renderResults(results) {
        if (!results || results.length === 0) {
            resultsContainer.innerHTML = '<p class="apss-no-results">No results found.</p>';
            return;
        }

        resultsContainer.innerHTML = results.map(item => `
            <a href="${item.permalink}" class="apss-result-item">
                <div class="apss-result-image">
                    ${item.image ? `<img src="${item.image}" alt="${item.title}">` : ''}
                </div>
                <h3 class="apss-result-title">${item.title}</h3>
            </a>
        `).join('');
    }
});
