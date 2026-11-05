import Icon from './Icon';
import { useFavorites, useMyRoute } from '../lib/store';
import { toast } from './Toasts';

export function FavButton({ id, className = 'btn btn-outline btn-icon fav-btn', label = 'Guardar en favoritos' }) {
    const [favs, toggle] = useFavorites();
    const on = favs.includes(id);
    return (
        <button
            type="button" className={`${className} ${on ? 'is-fav' : ''}`} aria-pressed={on} aria-label={label}
            onClick={(e) => { e.stopPropagation(); toggle(id); toast(on ? 'Quitado de favoritos' : 'Guardado en favoritos'); }}
        >
            <Icon name={on ? 'heart-fill' : 'heart'} />
        </button>
    );
}

export function RouteButton({ id, compact = false, className }) {
    const [route, toggle] = useMyRoute();
    const on = route.includes(id);
    return (
        <button
            type="button" className={className ?? (compact ? 'add-route' : 'btn btn-outline')} aria-pressed={on}
            aria-label={on ? 'Quitar de mi ruta' : 'Agregar a mi ruta'}
            onClick={(e) => { e.stopPropagation(); toggle(id); toast(on ? 'Quitado de tu ruta' : 'Agregado a tu ruta'); }}
        >
            <Icon name={on ? 'check' : 'plus'} />{!compact && <span>{on ? 'En mi ruta' : 'Agregar a mi ruta'}</span>}
        </button>
    );
}
