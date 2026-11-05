import { useEffect, useState } from 'react';
import Icon from './Icon';

/** Avisos breves. Uso: toast('Texto', 'info' | 'alert' | 'ok'). */
export function toast(text, type = 'ok') {
    window.dispatchEvent(new CustomEvent('vpc:toast', { detail: { text, type, id: Math.random() } }));
}

export default function Toasts() {
    const [items, setItems] = useState([]);
    useEffect(() => {
        const on = (e) => {
            const t = e.detail;
            setItems((list) => [...list, t]);
            setTimeout(() => setItems((list) => list.filter((x) => x.id !== t.id)), 3200);
        };
        window.addEventListener('vpc:toast', on);
        return () => window.removeEventListener('vpc:toast', on);
    }, []);
    return (
        <div className="toasts" aria-live="polite">
            {items.map((t) => (
                <div key={t.id} className="toast">
                    <Icon name={t.type === 'alert' ? 'alert' : t.type === 'info' ? 'info' : 'check'} /><span>{t.text}</span>
                </div>
            ))}
        </div>
    );
}
