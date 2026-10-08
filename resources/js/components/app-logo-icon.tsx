import type { SVGAttributes } from 'react';

/** Eenvoudig VD-merk: staalvorm met signaalstreep. */
export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
            <path
                fill="currentColor"
                d="M4 6h10.5c4.4 0 7.5 2.8 7.5 7s-3.1 7-7.5 7H8v6H4V6zm4 4v6h6.2c2.1 0 3.3-1.2 3.3-3s-1.2-3-3.3-3H8z"
            />
            <path
                fill="currentColor"
                opacity="0.9"
                d="M22 8h6v4h-6V8zm0 6h6v12h-6V14z"
            />
        </svg>
    );
}
