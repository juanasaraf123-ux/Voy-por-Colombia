import { u } from '../lib/url';

/** Ícono del sprite SVG (public/assets/img/icons.svg). */
export default function Icon({ name, className = 'icon', ...rest }) {
    return (
        <svg className={className} aria-hidden="true" focusable="false" {...rest}>
            <use href={`${u('/assets/img/icons.svg')}#${name}`} />
        </svg>
    );
}
