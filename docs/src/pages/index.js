import clsx from 'clsx';
import Link from '@docusaurus/Link';
import useDocusaurusContext from '@docusaurus/useDocusaurusContext';
import Layout from '@theme/Layout';
import HomepageFeatures from '@site/src/components/HomepageFeatures';

import Heading from '@theme/Heading';
import styles from './index.module.css';

function HomepageHeader() {
   const { siteConfig } = useDocusaurusContext();
   return (
      <header className={clsx('hero hero--primary', styles.heroBanner)}>
         <div className="container">
            <img className="hero__image" src={require('@site/static/img/logo2.png').default} alt="Logo" height={300} />
            <br />
            <Heading as="h1" className="hero__title">
               {siteConfig.title}
            </Heading>
            <p className="hero__subtitle">{siteConfig.tagline}</p>
            <div className={styles.buttons}>
               <Link className="button button--secondary button--lg" to="/docs/getting-started">
                  Documentation
               </Link>
            </div>
         </div>
      </header>
   );
}

export default function Home() {
   const { siteConfig } = useDocusaurusContext();
   return (
      <Layout title={`${siteConfig.title}`} description="Description will go into a meta tag in <head />">
         <HomepageHeader />
         <main>
            <HomepageFeatures />
            <section className={styles.comparisonSection}>
               <div className="container">
                  <div className="row">
                     <div className="col col--10 col--offset-1">
                        <Heading as="h2" className="text--center" style={{ marginBottom: '2rem' }}>Why another Laravel translation package?</Heading>
                        <p className="text--center" style={{ fontSize: '1.1rem', marginBottom: '3rem' }}>
                           While packages like <code className={styles.packageName}>spatie/laravel-translatable</code> and <code className={styles.packageName}>astrotomic/laravel-translatable</code> are powerful, they all make a trade-off. This package is built for raw performance and a clean, focused developer experience by using direct, indexed database queries instead of relying on JSON columns or Eloquent model hydration.
                        </p>

                        <div className={styles.tableContainer}>
                           <div className={styles.comparisonTable}>
                              <div className={styles.comparisonRow}>
                                 <div className={styles.comparisonHeader}>&nbsp;</div>
                                 <div className={`${styles.comparisonHeader} ${styles.ownPackage}`}>aaix/eloquent-translatable</div>
                                 <div className={styles.comparisonHeader}>spatie/laravel-translatable</div>
                                 <div className={styles.comparisonHeader}>astrotomic/laravel-translatable</div>
                              </div>
                              <div className={styles.comparisonRow}>
                                 <div className={styles.comparisonCell}><strong>Storage</strong></div>
                                 <div className={`${styles.comparisonCell} ${styles.ownPackage}`}>Dedicated Table</div>
                                 <div className={styles.comparisonCell}>JSON Column</div>
                                 <div className={styles.comparisonCell}>Dedicated Table</div>
                              </div>
                              <div className={styles.comparisonRow}>
                                 <div className={styles.comparisonCell}><strong>Performance</strong></div>
                                 <div className={`${styles.comparisonCell} ${styles.ownPackage}`}>High (Direct Queries)</div>
                                 <div className={styles.comparisonCell}>Medium (JSON Parsing)</div>
                                 <div className={styles.comparisonCell}>Low (Eloquent Overhead)</div>
                              </div>
                              <div className={styles.comparisonRow}>
                                 <div className={styles.comparisonCell}><strong>Simplicity</strong></div>
                                 <div className={`${styles.comparisonCell} ${styles.ownPackage}`}>Minimal API, Zero-Config Logic</div>
                                 <div className={styles.comparisonCell}>Requires managing JSON paths & complex queries</div>
                                 <div className={styles.comparisonCell}>Requires extra Translation Model class</div>
                              </div>
                           </div>
                        </div>

                        <hr className={styles.separator} />

                        <div className={styles.featureComparison}>
                           <h4><span className={styles.badge}>SUPERIOR</span> Database Performance</h4>
                           <p>By using direct, indexed database queries instead of parsing JSON or hydrating countless Eloquent models, operations are significantly faster and use a fraction of the memory. This is not a minor improvement; it's a fundamental architectural advantage for applications at scale.</p>
                        </div>

                        <div className={styles.featureComparison}>
                           <h4><span className={styles.badge}>BETTER</span> Data Integrity</h4>
                           <p>Translations are stored in a clean, normalized, and dedicated table. This provides better data integrity and structure than a single JSON column and avoids the performance pitfalls of a full Eloquent-relation approach.</p>
                        </div>

                        <div className={styles.featureComparison}>
                           <h4><span className={styles.badge}>SIMPLER</span> Developer Experience</h4>
                           <p>The API is designed to be minimal, intuitive, and predictable. With a single command to set up your migrations and a fluent, easy-to-understand set of methods, you get the power you need without the complexity you don't. No magic, just performance.</p>
                        </div>

                     </div>
                  </div>
               </div>
            </section>
         </main>
      </Layout>
   );
}
