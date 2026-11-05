import { Head, usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import Explorer from '../map/Explorer';

/** Portada: una sola pantalla fija con el mapa interactivo. */
export default function Home(props) {
    const { site } = usePage().props;
    useEffect(() => {
        document.documentElement.classList.add('is-fullscreen');
        return () => document.documentElement.classList.remove('is-fullscreen');
    }, []);
    return (
        <>
            <Head><meta name="description" content={props.texts.subtitle} /></Head>
            {props.heroImage && <img className="explore-bg" src={props.heroImage} alt="" aria-hidden="true" />}
            <Explorer {...props} tiles={site.tiles} />
        </>
    );
}
