(function () {
    'use strict';
    window.SideQuest.CustomDropdown = class {
        constructor(selectElement) {
            this.select = selectElement;
            this.options = Array.from(this.select.options);
            this.selectedIndex = this.select.selectedIndex;
            this.isOpen = false;
            this.createCustomDropdown();
            this.attachEvents();
            if (!window.customComponents) window.customComponents = [];
            window.customComponents.push(this);
        }

        createCustomDropdown() {
            this.wrapper = document.createElement('div');
            this.wrapper.className = 'custom-dropdown';
            if (this.select.dataset.dropdownSize) {
                this.wrapper.classList.add(`custom-dropdown-${this.select.dataset.dropdownSize}`);
            }

            this.backdrop = document.createElement('div');
            this.backdrop.className = 'custom-component-backdrop';
            this.backdrop.style.display = 'none';

            this.button = document.createElement('button');
            this.button.type = 'button';
            this.button.className = 'custom-dropdown-button';
            this.button.setAttribute('aria-haspopup', 'listbox');
            this.button.setAttribute('aria-expanded', 'false');
            this.button.innerHTML = `
                <span class="custom-dropdown-text">${this.options[this.selectedIndex]?.text || 'Select'}</span>
                <svg class="custom-dropdown-arrow" width="12" height="8" viewBox="0 0 12 8" fill="none">
                    <path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            `;

            this.list = document.createElement('ul');
            this.list.className = 'custom-dropdown-list';
            this.list.setAttribute('role', 'listbox');

            this.options.forEach((option, index) => {
                if (option.hidden) {
                    return;
                }
                const li = document.createElement('li');
                li.className = 'custom-dropdown-option';
                li.textContent = option.text;
                li.dataset.value = option.value;
                li.dataset.index = String(index);
                li.setAttribute('role', 'option');
                if (option.disabled) li.classList.add('disabled');
                if (index === this.selectedIndex) li.classList.add('selected');
                li.setAttribute('aria-selected', index === this.selectedIndex ? 'true' : 'false');
                this.list.appendChild(li);
            });

            this.wrapper.appendChild(this.button);
            this.wrapper.appendChild(this.list);
            document.body.appendChild(this.backdrop);

            this.select.style.display = 'none';
            this.select.parentNode.insertBefore(this.wrapper, this.select.nextSibling);
        }

        attachEvents() {
            this.button.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggle();
            });

            this.list.addEventListener('click', (e) => {
                const option = e.target.closest('.custom-dropdown-option');
                if (!option || option.classList.contains('disabled')) return;
                this.selectOption(parseInt(option.dataset.index, 10));
            });

            this.backdrop.addEventListener('click', () => this.close());

            document.addEventListener('click', (e) => {
                if (this.isOpen && !this.wrapper.contains(e.target)) this.close();
            });
        }

        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                if (window.customComponents) {
                    window.customComponents.forEach((component) => {
                        if (component !== this && component.isOpen) component.close();
                    });
                }
                this.open();
            }
        }

        open() {
            this.wrapper.classList.add('open');
            this.backdrop.style.display = 'block';
            this.isOpen = true;
            this.button.setAttribute('aria-expanded', 'true');
            if (window.innerWidth <= 640) document.body.style.overflow = 'hidden';
        }

        close() {
            this.wrapper.classList.remove('open');
            this.backdrop.style.display = 'none';
            this.isOpen = false;
            this.button.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        selectOption(index) {
            this.select.selectedIndex = index;
            this.select.dispatchEvent(new Event('change', { bubbles: true }));
            this.selectedIndex = index;
            this.button.querySelector('.custom-dropdown-text').textContent = this.options[index].text;
            this.list.querySelectorAll('.custom-dropdown-option').forEach((opt, i) => {
                opt.classList.toggle('selected', i === index);
                opt.setAttribute('aria-selected', i === index ? 'true' : 'false');
            });
            this.close();
        }
    };

    window.SideQuest.CustomCalendar = class {
        constructor(inputElement) {
            this.input = inputElement;
            this.selectedDate = this.input.value ? new Date(this.input.value) : null;
            this.currentMonth = this.selectedDate ? new Date(this.selectedDate) : new Date();
            this.isOpen = false;
            this.monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            this.dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
            this.createCustomCalendar();
            this.attachEvents();
            if (!window.customComponents) window.customComponents = [];
            window.customComponents.push(this);
        }

        createCustomCalendar() {
            this.wrapper = document.createElement('div');
            this.wrapper.className = 'custom-calendar';
            if (this.input.dataset.calendarSize) {
                this.wrapper.classList.add(`custom-calendar-${this.input.dataset.calendarSize}`);
            }

            this.backdrop = document.createElement('div');
            this.backdrop.className = 'custom-component-backdrop';
            this.backdrop.style.display = 'none';

            this.inputWrap = document.createElement('div');
            this.inputWrap.className = 'custom-calendar-input-wrap';

            this.trigger = document.createElement('button');
            this.trigger.type = 'button';
            this.trigger.className = 'custom-calendar-trigger';
            this.trigger.setAttribute('aria-label', 'Open calendar');
            this.trigger.setAttribute('aria-haspopup', 'dialog');
            this.trigger.setAttribute('aria-expanded', 'false');
            this.trigger.innerHTML = `
                <svg class="custom-calendar-icon" width="18" height="18" viewBox="0 0 20 20" fill="none">
                    <rect x="3" y="4" width="14" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M3 8H17" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M7 2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M13 2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            `;

            this.popup = document.createElement('div');
            this.popup.className = 'custom-calendar-popup';

            this.input.classList.add('custom-calendar-input');
            this.input.parentNode.insertBefore(this.wrapper, this.input);
            this.wrapper.appendChild(this.inputWrap);
            this.inputWrap.appendChild(this.input);
            this.inputWrap.appendChild(this.trigger);
            this.wrapper.appendChild(this.popup);
            document.body.appendChild(this.backdrop);

            this.syncFromInput();
            this.renderCalendar();
        }

        syncFromInput() {
            const value = (this.input.value || '').trim();
            if (!value) {
                this.selectedDate = null;
                return;
            }

            const parsed = new Date(`${value}T00:00:00`);
            if (!Number.isNaN(parsed.getTime())) {
                this.selectedDate = parsed;
                this.currentMonth = new Date(parsed);
            }
        }

        renderCalendar() {
            const year = this.currentMonth.getFullYear();
            const month = this.currentMonth.getMonth();
            this.popup.innerHTML = `
                <div class="custom-calendar-header">
                    <button type="button" class="custom-calendar-nav" data-action="prev-month">‹</button>
                    <div class="custom-calendar-title">${this.monthNames[month]} ${year}</div>
                    <button type="button" class="custom-calendar-nav" data-action="next-month">›</button>
                </div>
                <div class="custom-calendar-days">
                    ${this.dayNames.map((day) => `<div class="custom-calendar-day-name">${day}</div>`).join('')}
                </div>
                <div class="custom-calendar-dates">
                    ${this.renderDates(year, month)}
                </div>
                <div class="custom-calendar-pickers">
                    <div class="custom-picker-wrapper">
                        <div class="custom-picker-overlay"></div>
                        <div class="custom-picker month-picker" data-type="month">
                            ${this.renderMonthPicker(month)}
                        </div>
                    </div>
                    <div class="custom-picker-wrapper">
                        <div class="custom-picker-overlay"></div>
                        <div class="custom-picker year-picker" data-type="year">
                            ${this.renderYearPicker(year)}
                        </div>
                    </div>
                </div>
                <div class="custom-calendar-footer">
                    <button type="button" class="custom-calendar-action" data-action="clear">Clear</button>
                    <button type="button" class="custom-calendar-action" data-action="today">Today</button>
                </div>
            `;

            this.initializePickers();
        }

        renderMonthPicker(selectedMonth) {
            return this.monthNames.map((name, idx) => `<div class="picker-item ${idx === selectedMonth ? 'selected' : ''}" data-value="${idx}">${name}</div>`).join('');
        }

        renderYearPicker(selectedYear) {
            const currentYear = new Date().getFullYear();
            const years = [];
            for (let y = currentYear - 100; y <= currentYear + 10; y++) {
                years.push(`<div class="picker-item ${y === selectedYear ? 'selected' : ''}" data-value="${y}">${y}</div>`);
            }
            return years.join('');
        }

        initializePickers() {
            const monthPicker = this.popup.querySelector('.month-picker');
            const yearPicker = this.popup.querySelector('.year-picker');

            if (monthPicker) {
                this.setupPicker(monthPicker, 'month');
                this.scrollToSelected(monthPicker);
            }

            if (yearPicker) {
                this.setupPicker(yearPicker, 'year');
                this.scrollToSelected(yearPicker);
            }
        }

        setupPicker(picker, type) {
            let scrollTimeout;
            let isClickSelection = false;

            picker.addEventListener('scroll', () => {
                if (isClickSelection || picker.dataset.autoScrolling === 'true') {
                    return;
                }

                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    this.snapToNearest(picker, type, false);
                }, 150);
            });

            picker.addEventListener('click', (e) => {
                const item = e.target.closest('.picker-item');
                if (!item) {
                    return;
                }

                isClickSelection = true;
                const value = parseInt(item.dataset.value, 10);
                if (type === 'month') {
                    this.currentMonth.setMonth(value);
                } else {
                    this.currentMonth.setFullYear(value);
                }

                picker.querySelectorAll('.picker-item').forEach((i) => i.classList.remove('selected'));
                item.classList.add('selected');

                const offset = this.getPickerScrollTarget(picker, item);
                this.scrollPicker(picker, offset, 'smooth');
                this.updateHeaderTitle();

                setTimeout(() => {
                    isClickSelection = false;
                }, 500);
            });
        }

        snapToNearest(picker, type, shouldRender = true) {
            const items = picker.querySelectorAll('.picker-item');
            const pickerCenter = picker.scrollTop + (picker.clientHeight / 2);
            let closestItem = null;
            let closestDistance = Infinity;

            items.forEach((item) => {
                const itemCenter = item.offsetTop + (item.offsetHeight / 2);
                const distance = Math.abs(itemCenter - pickerCenter);
                if (distance < closestDistance) {
                    closestDistance = distance;
                    closestItem = item;
                }
            });

            if (!closestItem) {
                return;
            }

            const targetScrollTop = this.getPickerScrollTarget(picker, closestItem);
            if (Math.abs(picker.scrollTop - targetScrollTop) > 1) {
                this.scrollPicker(picker, targetScrollTop);
            }

            items.forEach((item) => item.classList.remove('selected'));
            closestItem.classList.add('selected');

            const value = parseInt(closestItem.dataset.value, 10);
            if (type === 'month') {
                this.currentMonth.setMonth(value);
            } else {
                this.currentMonth.setFullYear(value);
            }

            if (shouldRender) {
                this.renderCalendar();
            } else {
                this.updateHeaderTitle();
            }
        }

        updateHeaderTitle() {
            const titleElement = this.popup.querySelector('.custom-calendar-title');
            if (titleElement) {
                const year = this.currentMonth.getFullYear();
                const month = this.currentMonth.getMonth();
                titleElement.textContent = `${this.monthNames[month]} ${year}`;
            }

            const datesContainer = this.popup.querySelector('.custom-calendar-dates');
            if (datesContainer) {
                const year = this.currentMonth.getFullYear();
                const month = this.currentMonth.getMonth();
                datesContainer.innerHTML = this.renderDates(year, month);
            }
        }

        scrollToSelected(picker) {
            const selected = picker.querySelector('.picker-item.selected');
            if (!selected) {
                return;
            }
            const offset = this.getPickerScrollTarget(picker, selected);
            this.scrollPicker(picker, offset);
        }

        getPickerScrollTarget(picker, item) {
            return item.offsetTop - (picker.clientHeight / 2) + (item.offsetHeight / 2);
        }

        scrollPicker(picker, top, behavior = 'auto') {
            const maxScrollTop = Math.max(0, picker.scrollHeight - picker.clientHeight);
            const clampedTop = Math.max(0, Math.min(top, maxScrollTop));

            clearTimeout(picker._autoScrollTimer);
            picker.dataset.autoScrolling = 'true';
            picker.scrollTo({ top: clampedTop, behavior });

            const resetDelay = behavior === 'smooth' ? 350 : 0;
            picker._autoScrollTimer = setTimeout(() => {
                picker.dataset.autoScrolling = 'false';
            }, resetDelay);
        }

        renderDates(year, month) {
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();
            let html = '';

            for (let i = firstDay - 1; i >= 0; i--) {
                html += `<button type="button" class="custom-calendar-date other-month" data-date="${year}-${month}-${daysInPrevMonth - i}">${daysInPrevMonth - i}</button>`;
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dateStr = this.formatDateISO(date);
                const isSelected = this.selectedDate && this.formatDateISO(this.selectedDate) === dateStr;
                const isToday = this.formatDateISO(new Date()) === dateStr;
                let classes = 'custom-calendar-date';
                if (isSelected) classes += ' selected';
                if (isToday) classes += ' today';
                html += `<button type="button" class="${classes}" data-date="${dateStr}">${day}</button>`;
            }

            const totalCells = firstDay + daysInMonth;
            const remainingCells = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
            for (let i = 1; i <= remainingCells; i++) {
                html += `<button type="button" class="custom-calendar-date other-month" data-date="${year}-${month + 2}-${i}">${i}</button>`;
            }

            return html;
        }

        attachEvents() {
            this.trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggle();
            });

            this.input.addEventListener('input', () => {
                this.syncFromInput();
                if (this.isOpen) {
                    this.renderCalendar();
                }
            });

            this.input.addEventListener('change', () => {
                this.syncFromInput();
                if (this.isOpen) {
                    this.renderCalendar();
                }
            });

            this.popup.addEventListener('click', (e) => {
                e.stopPropagation();
                const target = e.target.closest('[data-action], [data-date]');
                if (!target) return;
                const action = target.dataset.action;
                const date = target.dataset.date;

                if (action === 'prev-month') {
                    this.currentMonth.setMonth(this.currentMonth.getMonth() - 1);
                    this.renderCalendar();
                } else if (action === 'next-month') {
                    this.currentMonth.setMonth(this.currentMonth.getMonth() + 1);
                    this.renderCalendar();
                } else if (action === 'clear') {
                    this.selectDate(null);
                } else if (action === 'today') {
                    this.selectDate(new Date());
                } else if (date) {
                    this.selectDate(new Date(date));
                }
            });

            this.backdrop.addEventListener('click', () => this.close());
            document.addEventListener('click', (e) => {
                if (this.isOpen && !this.wrapper.contains(e.target)) this.close();
            });
        }

        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                if (window.customComponents) {
                    window.customComponents.forEach((component) => {
                        if (component !== this && component.isOpen) component.close();
                    });
                }
                this.open();
            }
        }

        open() {
            this.syncFromInput();
            this.renderCalendar();
            this.wrapper.classList.add('open');
            this.backdrop.style.display = 'block';
            this.isOpen = true;
            this.trigger.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        close() {
            this.wrapper.classList.remove('open');
            this.backdrop.style.display = 'none';
            this.isOpen = false;
            this.trigger.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        selectDate(date) {
            this.selectedDate = date;
            this.input.value = date ? this.formatDateISO(date) : '';
            this.input.dispatchEvent(new Event('change', { bubbles: true }));
            if (date) this.currentMonth = new Date(date);
            this.renderCalendar();
            if (date) this.close();
        }

        formatDate(date) {
            if (!date) return '';
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        formatDateISO(date) {
            if (!date) return '';
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    };

    window.SideQuest.initCustomDropdowns = function () {
        document.querySelectorAll('select.custom-select').forEach((select) => {
            if (select.dataset.customDropdownBound === 'true') {
                return;
            }
            select.dataset.customDropdownBound = 'true';
            new window.SideQuest.CustomDropdown(select);
        });
    };

    window.SideQuest.initCustomDatePickers = function () {
        document.querySelectorAll('input[type="date"].custom-date').forEach((input) => {
            if (input.dataset.customCalendarBound === 'true') {
                return;
            }
            input.dataset.customCalendarBound = 'true';
            new window.SideQuest.CustomCalendar(input);
        });
    };
})();
