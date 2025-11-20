document.addEventListener('DOMContentLoaded', () => {
   // Store original heights for each Select2 instance
   const originalHeights = new Map();

   // When a Select2 dropdown opens
   $(document).on('select2:open', (e) => {
      if(e.target.classList.value.includes("expandOnFocus") === false) {
         return;
      }

      const selectEl = e.target;
      const $select = $(selectEl);
      const $container = $select.next('.select2-container');

      if ($container.length) {
         // Store the original height before we change it
         if (!originalHeights.has(selectEl.id)) {
            const originalHeight = $container.find('.select2-selection').outerHeight();
            originalHeights.set(selectEl.id, originalHeight);
         }

         // Expand the dropdown container
         $container.css({
            'height': 'auto',
            'min-height': '75px'
         });
         
         $container.find('.select2-selection--multiple').css({
            'height': 'auto',
            'min-height': '75px',
            'max-height': '150',
            'overflow-y': 'auto'
         });
         
         $container.find('.select2-selection--single').css({
            'height': 'auto',
            'min-height': '38px'
         });
      }
   });

   // When a Select2 dropdown closes (loses focus)
   $(document).on('select2:close', (e) => {

      if(e.target.classList.value.includes("expandOnFocus") === false) {
         return;
      }

      const selectEl = e.target;
      const $select = $(selectEl);
      const $container = $select.next('.select2-container');

      if ($container.length) {
         // Reset to original height
         const originalHeight = originalHeights.get(selectEl.id) || '38px';
         
         $container.css({
            'height': originalHeight,
            'min-height': ''
         });
         
         $container.find('.select2-selection--multiple').css({
            'height': originalHeight,
            'min-height': '',
            'max-height': '',
            'overflow-y': ''
         });
         
         $container.find('.select2-selection--single').css({
            'height': originalHeight,
            'min-height': ''
         });
      }
   });
});