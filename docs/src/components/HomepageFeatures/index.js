import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

const FeatureList = [

   {
      title: 'Developer-First Experience',
      img: require('@site/static/img/developer.png').default,
      description: (
         <>
            Get productive in seconds. Scaffold your migration files with a single Artisan command, and write safer, more readable
            code with the built-in Locale enum that prevents typos and improves clarity.
         </>
      ),
   },
   {
      title: 'Intuitive & Fluent API',
      img: require('@site/static/img/api.png').default,
      description: (
         <>
            Designed with the developer in mind. Its clean and consistent API lets you manage translations with expressive,
            readable code, allowing you to get started quickly and maintain your projects with ease.
         </>
      ),
   },
   {
      title: 'Performant & Scalable',
      img: require('@site/static/img/performance.png').default,
      description: (
         <>
            Built for speed and scalability. By intentionally bypassing Eloquent's overhead for direct database queries, this
            package delivers lightning-fast performance with minimal memory usage, even when handling millions of translations.
         </>
      ),
   },
];

function Feature({ img, title, description }) {
   return (
      <div className={clsx('col col--4')}>
         <div className="text--center">
            <img className="hero__image" src={img} alt="Logo" height={100} role="img" />
         </div>
         <div className="text--center padding-horiz--md">
            <Heading as="h3">{title}</Heading>
            <p>{description}</p>
         </div>
      </div>
   );
}

export default function HomepageFeatures() {
   return (
      <section className={styles.features}>
         <div className="container" style={{ marginTop: '2rem' }}>
            <div className="row">
               {FeatureList.map((props, idx) => (
                  <Feature key={idx} {...props} />
               ))}
            </div>
         </div>
      </section>
   );
}
