function showForm(formId) {
    hideForms();
    document.getElementById(formId).style.display = 'block';
}

function hideForms() {
    const forms = document.querySelectorAll('.form-container, .view-container');
    forms.forEach(form => form.style.display = 'none');
}


