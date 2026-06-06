/**
 * Central Client-side Logic Script
 * Fetches data from api.php, renders DOM elements dynamically,
 * and initializes animations and user interactions.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Current year helper
    document.getElementById('footer-year').textContent = new Date().getFullYear();

    // 1. Initial State / Configuration Variables
    const apiEndpoint = 'api.php';
    
    // --- Mobile Menu Toggle ---
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navMenu = document.getElementById('navMenu');
    
    mobileMenuBtn.addEventListener('click', () => {
        navMenu.classList.toggle('open');
        const icon = mobileMenuBtn.querySelector('i');
        if (navMenu.classList.contains('open')) {
            icon.className = 'fas fa-times';
        } else {
            icon.className = 'fas fa-bars';
        }
    });

    // Delegate menu closing when clicking nav links (including dynamic ones)
    navMenu.addEventListener('click', (e) => {
        if (e.target.classList.contains('nav-link')) {
            navMenu.classList.remove('open');
            mobileMenuBtn.querySelector('i').className = 'fas fa-bars';
            
            // Manage active nav class manually on click
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            e.target.classList.add('active');
        }
    });

    // --- Theme Switcher ---
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle.querySelector('i');
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'light' || (!savedTheme && !systemPrefersDark)) {
        document.documentElement.setAttribute('data-theme', 'light');
        themeIcon.className = 'fas fa-sun';
    } else {
        document.documentElement.removeAttribute('data-theme');
        themeIcon.className = 'fas fa-moon';
    }
    
    themeToggle.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        if (currentTheme === 'light') {
            document.documentElement.removeAttribute('data-theme');
            themeIcon.className = 'fas fa-moon';
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            themeIcon.className = 'fas fa-sun';
            localStorage.setItem('theme', 'light');
        }
    });

    // Helper to escape HTML and prevent XSS in client side
    function escapeHTML(str) {
        if (!str) return '';
        return str.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // --- Fetch and Render Portfolio Data ---
    async function loadPortfolio() {
        try {
            const response = await fetch(`${apiEndpoint}?action=get_portfolio`);
            const json = await response.json();
            
            if (json.status !== 'success') {
                console.error('API Error:', json.message);
                return;
            }
            
            const data = json.data;
            renderProfile(data.profile);
            renderEducation(data.education);
            renderSkills(data.skills);
            renderProjects(data.projects);
            renderCustomSections(data.custom_sections);
            
            // Initialize animations after DOM elements are fully loaded
            initializeAnimations();
            
        } catch (error) {
            console.error('Failed to load portfolio:', error);
        }
    }

    // Render profile and contact info
    function renderProfile(profile) {
        const nameEsced = escapeHTML(profile.name);
        
        // Headers and Footers
        document.getElementById('logo-name').textContent = nameEsced;
        document.getElementById('hero-name').textContent = nameEsced;
        document.getElementById('footer-name').textContent = nameEsced;
        
        document.getElementById('hero-title').textContent = escapeHTML(profile.title);
        document.getElementById('hero-bio-short').textContent = escapeHTML(profile.bio_short);
        document.getElementById('about-bio-full').innerHTML = escapeHTML(profile.bio_full || profile.bio_short).replace(/\n/g, '<br>');
        
        // Profile Image
        const imgContainer = document.getElementById('hero-img-container');
        if (profile.profile_picture) {
            imgContainer.innerHTML = `<img src="images/uploads/${escapeHTML(profile.profile_picture)}" alt="${nameEsced}" class="hero-img">`;
        }
        
        // Resume Button
        if (profile.resume_url && profile.resume_url !== '#') {
            const heroButtons = document.getElementById('hero-buttons');
            const resumeBtn = document.createElement('a');
            resumeBtn.href = escapeHTML(profile.resume_url);
            resumeBtn.className = 'btn btn-secondary';
            resumeBtn.target = '_blank';
            resumeBtn.innerHTML = `Download Resume <i class="fas fa-download"></i>`;
            heroButtons.appendChild(resumeBtn);
        }
        
        // Social Links
        const socialsContainer = document.getElementById('hero-socials');
        const footerSocials = document.getElementById('footer-socials');
        let socialHTML = '';
        
        if (profile.github) {
            socialHTML += `<a href="${escapeHTML(profile.github)}" target="_blank" aria-label="GitHub"><i class="fab fa-github"></i></a>`;
        }
        if (profile.linkedin) {
            socialHTML += `<a href="${escapeHTML(profile.linkedin)}" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>`;
        }
        if (profile.facebook) {
            socialHTML += `<a href="${escapeHTML(profile.facebook)}" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>`;
        }
        
        socialsContainer.innerHTML = socialHTML;
        footerSocials.innerHTML = socialHTML;
        
        // About details & Contact Details
        const aboutDetails = document.getElementById('about-details');
        const contactInfoList = document.getElementById('contact-info-list');
        let detailsHTML = '';
        let contactHTML = '';
        
        if (profile.email) {
            detailsHTML += `<li><strong>Email:</strong> <span>${escapeHTML(profile.email)}</span></li>`;
            contactHTML += `
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <h4>Email</h4>
                        <p><a href="mailto:${escapeHTML(profile.email)}">${escapeHTML(profile.email)}</a></p>
                    </div>
                </div>
            `;
        }
        if (profile.phone) {
            detailsHTML += `<li><strong>Phone:</strong> <span>${escapeHTML(profile.phone)}</span></li>`;
            contactHTML += `
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
                    <div>
                        <h4>Phone</h4>
                        <p>${escapeHTML(profile.phone)}</p>
                    </div>
                </div>
            `;
        }
        if (profile.location) {
            detailsHTML += `<li><strong>Location:</strong> <span>${escapeHTML(profile.location)}</span></li>`;
            contactHTML += `
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <h4>Location</h4>
                        <p>${escapeHTML(profile.location)}</p>
                    </div>
                </div>
            `;
        }
        
        aboutDetails.innerHTML = detailsHTML;
        contactInfoList.innerHTML = contactHTML;
    }

    // Render Education Timeline
    function renderEducation(educationList) {
        const timeline = document.getElementById('education-timeline');
        if (!educationList || educationList.length === 0) {
            timeline.innerHTML = '<p>No education details listed.</p>';
            return;
        }
        
        let timelineHTML = '';
        educationList.forEach(edu => {
            const resultBadge = edu.result ? `<span class="timeline-result">${escapeHTML(edu.result)}</span>` : '';
            timelineHTML += `
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-date">${escapeHTML(edu.year)}</div>
                    <h4 class="timeline-title">${escapeHTML(edu.degree)}</h4>
                    <p class="timeline-institution">${escapeHTML(edu.institution)}</p>
                    ${resultBadge}
                </div>
            `;
        });
        
        timeline.innerHTML = timelineHTML;
    }

    // Render Skills Section
    function renderSkills(groupedSkills) {
        const skillsSection = document.getElementById('skills');
        const grid = document.getElementById('skills-grid');
        
        if (!groupedSkills || Object.keys(groupedSkills).length === 0) {
            skillsSection.style.display = 'none';
            return;
        }
        
        skillsSection.style.display = 'block';
        let gridHTML = '';
        
        for (const [category, skills] of Object.entries(groupedSkills)) {
            let skillItemsHTML = '';
            skills.forEach(skill => {
                skillItemsHTML += `
                    <div class="skill-item">
                        <div class="skill-info">
                            <span>${escapeHTML(skill.name)}</span>
                            <span>${parseInt(skill.proficiency)}%</span>
                        </div>
                        <div class="skill-bar-bg">
                            <div class="skill-bar-fill" style="width: ${parseInt(skill.proficiency)}%"></div>
                        </div>
                    </div>
                `;
            });
            
            gridHTML += `
                <div class="skills-card reveal-up">
                    <h3>${escapeHTML(category)}</h3>
                    <div class="skills-list">
                        ${skillItemsHTML}
                    </div>
                </div>
            `;
        }
        
        grid.innerHTML = gridHTML;
    }

    // Render Projects Section
    function renderProjects(projectsList) {
        const projectsSection = document.getElementById('projects');
        const grid = document.getElementById('projects-grid');
        
        if (!projectsList || projectsList.length === 0) {
            projectsSection.style.display = 'none';
            return;
        }
        
        projectsSection.style.display = 'block';
        let gridHTML = '';
        
        projectsList.forEach(proj => {
            // Build tool tags
            const tags = proj.tools.split(',')
                .map(t => t.trim())
                .filter(t => t !== '')
                .map(t => `<span class="tool-tag">${escapeHTML(t)}</span>`)
                .join('');
                
            // Image template
            let imageHTML = `<div class="project-img-fallback"><i class="fas fa-laptop-code"></i></div>`;
            if (proj.image) {
                imageHTML = `<img src="images/uploads/${escapeHTML(proj.image)}" alt="${escapeHTML(proj.title)}">`;
            }
            
            // Link buttons
            let linksHTML = '';
            if (proj.github_link && proj.github_link !== '#') {
                linksHTML += `<a href="${escapeHTML(proj.github_link)}" target="_blank" class="proj-link"><i class="fab fa-github"></i> Code</a>`;
            }
            if (proj.live_link && proj.live_link !== '#') {
                linksHTML += `<a href="${escapeHTML(proj.live_link)}" target="_blank" class="proj-link"><i class="fas fa-external-link-alt"></i> Live Demo</a>`;
            }
            
            gridHTML += `
                <div class="project-card reveal-up">
                    <div class="project-img-box">
                        ${imageHTML}
                    </div>
                    <div class="project-content">
                        <h3>${escapeHTML(proj.title)}</h3>
                        <p>${escapeHTML(proj.description)}</p>
                        <div class="project-tools">
                            ${tags}
                        </div>
                        <div class="project-links">
                            ${linksHTML}
                        </div>
                    </div>
                </div>
            `;
        });
        
        grid.innerHTML = gridHTML;
    }

    // Render Dynamic Custom Sections
    function renderCustomSections(sections) {
        const wrapper = document.getElementById('custom-sections-wrapper');
        const navMenu = document.getElementById('navMenu');
        const contactLink = navMenu.querySelector('a[href="#contact"]');
        
        // Remove existing custom section elements and nav links
        document.querySelectorAll('.custom-section').forEach(el => el.remove());
        document.querySelectorAll('.nav-link-custom').forEach(el => el.remove());
        
        if (!sections || sections.length === 0) return;
        
        sections.forEach(sec => {
            const secId = `sec-${sec.id}`;
            const secTitle = escapeHTML(sec.title);
            
            // 1. Create Navigation Link dynamically
            const navLink = document.createElement('a');
            navLink.href = `#${secId}`;
            navLink.className = 'nav-link nav-link-custom';
            navLink.textContent = secTitle;
            // Insert custom nav links right before 'Contact'
            navMenu.insertBefore(navLink, contactLink);
            
            // 2. Render Cards inside Section
            let itemsHTML = '';
            if (sec.items && sec.items.length > 0) {
                sec.items.forEach(item => {
                    const subtitleHTML = item.item_subtitle ? `<span class="custom-item-subtitle">${escapeHTML(item.item_subtitle)}</span>` : '';
                    const dateHTML = item.item_date ? `<span class="custom-item-date">${escapeHTML(item.item_date)}</span>` : '';
                    const descHTML = item.item_description ? `<p class="custom-item-desc">${escapeHTML(item.item_description).replace(/\n/g, '<br>')}</p>` : '';
                    const linkHTML = (item.item_link && item.item_link !== '#') ? `<a href="${escapeHTML(item.item_link)}" class="custom-item-link" target="_blank">Learn More <i class="fas fa-arrow-right"></i></a>` : '';
                    
                    itemsHTML += `
                        <div class="custom-item-card reveal-up">
                            <div class="custom-item-header">
                                <div>
                                    <h3>${escapeHTML(item.item_title)}</h3>
                                    ${subtitleHTML}
                                </div>
                                ${dateHTML}
                            </div>
                            ${descHTML}
                            ${linkHTML}
                        </div>
                    `;
                });
            } else {
                itemsHTML = '<p class="reveal-up text-center">No items added to this section yet.</p>';
            }
            
            // 3. Create Section Container
            const sectionEl = document.createElement('section');
            sectionEl.id = secId;
            sectionEl.className = 'section custom-section';
            sectionEl.innerHTML = `
                <div class="container">
                    <div class="section-title reveal-up">
                        <h2>${secTitle}</h2>
                        <div class="title-underline"></div>
                    </div>
                    <div class="custom-sec-grid">
                        ${itemsHTML}
                    </div>
                </div>
            `;
            
            wrapper.appendChild(sectionEl);
        });
    }

    // --- Active Link Highlight on Scroll (Scroll Spy) ---
    function initializeScrollSpy() {
        const navMenu = document.getElementById('navMenu');
        
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section[id]');
            let scrollY = window.pageYOffset;
            
            sections.forEach(current => {
                const sectionHeight = current.offsetHeight;
                const sectionTop = current.offsetTop - 120; // Account for header offset
                const sectionId = current.getAttribute('id');
                
                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    navMenu.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
                    const activeLink = navMenu.querySelector(`.nav-link[href*="${sectionId}"]`);
                    if (activeLink) {
                        activeLink.classList.add('active');
                    }
                }
            });
        });
    }

    // --- Intersection Observer Animations ---
    function initializeAnimations() {
        const revealElements = document.querySelectorAll('.reveal-up, .reveal-left, .reveal-right');
        
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('reveal-active');
                    // Unobserve to trigger transition once
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -40px 0px'
        });

        revealElements.forEach(element => {
            revealObserver.observe(element);
        });
    }

    // --- Contact Form Submission handler ---
    const contactForm = document.getElementById('contactForm');
    const formAlert = document.getElementById('form-alert');
    
    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const name = document.getElementById('form-name').value.trim();
        const email = document.getElementById('form-email').value.trim();
        const subject = document.getElementById('form-subject').value.trim();
        const message = document.getElementById('form-message').value.trim();
        
        if (!name || !email || !subject || !message) {
            showAlert('All fields are required.', 'error');
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = `Sending... <i class="fas fa-spinner fa-spin"></i>`;
        
        try {
            const response = await fetch(`${apiEndpoint}?action=send_message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name, email, subject, message })
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                showAlert(result.message, 'success');
                contactForm.reset();
            } else {
                showAlert(result.message || 'Failed to send message. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Submission Error:', error);
            showAlert('Connection error. Failed to send message.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `Send Message <i class="fas fa-paper-plane"></i>`;
        }
    });
    
    function showAlert(msg, type) {
        formAlert.innerHTML = `
            <div class="alert alert-${type}">
                ${escapeHTML(msg)}
            </div>
        `;
        // Scroll to alert slightly
        formAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // 2. Start Execution
    loadPortfolio();
    initializeScrollSpy();
});
