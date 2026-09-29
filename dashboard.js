function showSection(id, button) {
    document.querySelectorAll(".section").forEach(section => {
        section.classList.remove("active");
    });

    const section = document.getElementById(id);
    if (section) {
        section.classList.add("active");
    }

    document.querySelectorAll(".side-link").forEach(link => {
        link.classList.remove("active");
    });

    if (button) {
        button.classList.add("active");
    }
}

function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}
