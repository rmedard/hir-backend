(function (Drupal) {
  'use strict';

  Drupal.behaviors.agentTabs = {
    attach: function (context, settings) {
      // Find all tab links that haven't been processed yet
      const tabLinks = context.querySelectorAll('.agent-tab-link:not([data-agent-tabs-processed])');

      tabLinks.forEach(function(tabLink) {
        // Mark as processed
        tabLink.setAttribute('data-agent-tabs-processed', 'true');

        tabLink.addEventListener('click', function(e) {
          e.preventDefault();

          const tab = this.getAttribute('data-tab');
          const nodeId = this.getAttribute('data-node-id');
          const contentElement = document.getElementById('tab-content-' + tab);

          if (!contentElement) {
            console.error('Tab content element not found:', 'tab-content-' + tab);
            return;
          }

          // Don't reload if already active and content exists
          if (this.classList.contains('active') && contentElement.children.length > 0) {
            return;
          }

          // Show loading spinner
          contentElement.innerHTML = `
            <div class="text-center p-4">
              <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
              <div class="mt-2">Loading...</div>
            </div>
          `;

          // Make AJAX request using fetch
          fetch('/agent/' + nodeId + '/tab/' + tab, {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
            .then(function(response) {
              if (!response.ok) {
                throw new Error('HTTP ' + response.status + ': ' + getErrorMessage(response.status));
              }
              return response.json();
            })
            .then(function(data) {
              if (data.content) {
                contentElement.innerHTML = data.content;
                // Trigger Drupal behaviors on new content
                Drupal.attachBehaviors(contentElement);
              } else {
                contentElement.innerHTML = '<div class="alert alert-warning">No content available</div>';
              }
            })
            .catch(function(error) {
              console.error('Error loading tab content:', error);
              contentElement.innerHTML = `
              <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> ${error.message}
              </div>
            `;
            });

          // Update active states
          updateTabStates(tab, tabLink);
        });
      });

      // Load first tab content on page load if not already loaded
      const firstActiveTab = context.querySelector('.agent-tab-link.active');
      if (firstActiveTab) {
        const firstTabId = firstActiveTab.getAttribute('data-tab');
        const firstTabContent = document.getElementById('tab-content-' + firstTabId);

        if (firstTabContent && firstTabContent.children.length === 0) {
          firstActiveTab.click();
        }
      }
    }
  };

  /**
   * Update tab active states
   */
  function updateTabStates(activeTab, activeLink) {
    // Remove active class from all tab links
    const allTabLinks = document.querySelectorAll('.agent-tab-link');
    allTabLinks.forEach(function(link) {
      link.classList.remove('active');
    });

    // Remove active classes from all tab panes
    const allTabPanes = document.querySelectorAll('.agent-tab-pane');
    allTabPanes.forEach(function(pane) {
      pane.classList.remove('show', 'active');
    });

    // Add active class to clicked link
    activeLink.classList.add('active');

    // Add active classes to corresponding tab pane
    const activePane = document.getElementById('tab-content-' + activeTab);
    if (activePane) {
      activePane.classList.add('show', 'active');
    }
  }

  /**
   * Get user-friendly error message
   */
  function getErrorMessage(status) {
    switch (status) {
      case 403:
        return 'Access denied';
      case 404:
        return 'Content not found';
      case 500:
        return 'Server error';
      default:
        return 'Error loading content';
    }
  }

})(Drupal);
