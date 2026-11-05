import { Link } from '@inertiajs/react';
import { u, useSite } from '../lib/url';

const PETALS = ['#2bb673', '#12b5b0', '#1d6fe0', '#7b3fe4', '#ff3f8e', '#ff4b3e', '#ff7a1a', '#ffc531'];

/** Isotipo: flor de ocho pétalos con los colores de los paisajes de Colombia. */
export function Logo() {
    return (
        <svg className="brand-mark" viewBox="-50 -50 100 100" aria-hidden="true">
            {PETALS.map((c, i) => (
                <path key={c} d="M0 -6 C 9 -16, 11 -34, 0 -46 C -11 -34, -9 -16, 0 -6 Z" fill={c} transform={`rotate(${i * 45 - 20})`} />
            ))}
            <circle r="6" fill="#fff" />
        </svg>
    );
}

export default function Brand() {
    const site = useSite();
    return (
        <Link className="brand" href={u('/')} aria-label={`${site.name}, ir al inicio`}>
            <Logo />
            <span className="brand-text">
                {site.logoPre && <span className="brand-pre">{site.logoPre}</span>}
                <span className="brand-name">{site.logoTitle}</span>
                <span className="brand-tag">{site.logoSubtitle}</span>
            </span>
        </Link>
    );
}
