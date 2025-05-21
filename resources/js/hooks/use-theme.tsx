import { useState, useEffect } from 'react';

type Theme = 'light' | 'dark' | 'system';

export function useTheme() {
    const [theme, setTheme] = useState<Theme>(() => {
        if (typeof window !== 'undefined') {
            const storedTheme = localStorage.getItem('theme');
            if (storedTheme === 'light' || storedTheme === 'dark' || storedTheme === 'system') {
                return storedTheme;
            }
            
            // Default to system if no preference is stored
            return 'system';
        }
        return 'system';
    });
    
    const [mounted, setMounted] = useState(false);

    useEffect(() => {
        setMounted(true);
    }, []);

    useEffect(() => {
        if (!mounted) return;
        
        const root = window.document.documentElement;

        // Remove existing class
        root.classList.remove('light', 'dark');

        // Apply theme
        if (theme === 'system') {
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            root.classList.add(systemTheme);
        } else {
            root.classList.add(theme);
        }

        // Store the preference
        localStorage.setItem('theme', theme);
        
        // For compatibility with Laravel's theme storage
        if (typeof window !== 'undefined') {
            const options = { path: '/' };
            document.cookie = `app_appearance=${theme};path=/;max-age=${60*60*24*365}`;
        }
    }, [theme, mounted]);

    return { theme, setTheme, mounted };
}
