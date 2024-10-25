import { useEffect, useState } from 'react';
import Cookies from 'js-cookie';

const ThemeToggler = () => {
    const [isDarkMode, setIsDarkMode] = useState(false);

    useEffect(() => {
        const theme = Cookies.get('theme');
        if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            setIsDarkMode(true);
        } else {
            document.documentElement.classList.remove('dark');
            setIsDarkMode(false);
        }
    }, []);

    const toggleTheme = () => {
        if (isDarkMode) {
            document.documentElement.classList.remove('dark');
            Cookies.set('theme', 'light', { expires: 365, secure: true, sameSite: 'Strict' });
        } else {
            document.documentElement.classList.add('dark');
            Cookies.set('theme', 'dark', { expires: 365, secure: true, sameSite: 'Strict' });
        }
        setIsDarkMode(!isDarkMode);
    };

    return (
        <button
            onClick={toggleTheme}
            className="p-2 rounded-full bg-transparent"
            aria-label="Toggle Theme"
        >
            {isDarkMode ? '🌙' : '☀️'}
        </button>
    );
};

export default ThemeToggler;