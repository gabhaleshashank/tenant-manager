// Basic client-side form validation helpers.

document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll('form[data-validate="true"]');

  forms.forEach((form) => {
    form.addEventListener('submit', (event) => {
      const requiredFields = form.querySelectorAll('[data-required="true"]');
      let hasError = false;

      requiredFields.forEach((field) => {
        const value = field.value.trim();
        if (!value) {
          hasError = true;
          field.classList.add('field-error');
        } else {
          field.classList.remove('field-error');
        }
      });

      if (hasError) {
        event.preventDefault();
        showFormError(form, 'Please fill in all required fields.');
      }
    });
  });
});

function showFormError(form, message) {
  let box = form.querySelector('.error-message.client');
  if (!box) {
    box = document.createElement('div');
    box.className = 'error-message client';
    form.prepend(box);
  }
  box.textContent = message;
}

