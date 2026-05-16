document.addEventListener('DOMContentLoaded', () => {
    // quiz timer
    const timerEl = document.getElementById('timer-display');
    const timerContainer = document.querySelector('.timer');
    const form = document.getElementById('quiz-form');

    if (timerEl && timerContainer && form) {
        let remaining = parseInt(timerContainer.dataset.timeLimit, 10);

        const updateDisplay = () => {
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            timerEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        };

        updateDisplay();
        const interval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(interval);
                timerEl.textContent = '0:00';
                form.submit(); // auto-submit when time is up
            } else {
                updateDisplay();
            }
        }, 1000);
    }

    // hide MCQ options when short-answer selected (admin_questions)
    const questionTypeSelect = document.getElementById('question_type');
    const mcqOptions = document.getElementById('mcq-options');
    if (questionTypeSelect && mcqOptions) {
        const toggleOptions = () => {
            if (questionTypeSelect.value === 'mcq') {
                mcqOptions.style.display = 'block';
            } else {
                mcqOptions.style.display = 'none';
            }
        };
        questionTypeSelect.addEventListener('change', toggleOptions);
        toggleOptions();
    }
});
