import React from "react";
import Layout from "@theme/Layout";
import Link from "@docusaurus/Link";
import Heading from "@theme/Heading";

export default function Home() {
  return (
    <Layout
      title="Laravel Migration Linter"
      description="Lint and analyze Laravel migrations before they break production."
    >
      <main className="hero-main">
        <section className="hero-section text-center">
          <Heading as="h1" className="hero-title">
            🧩 Laravel Migration Linter
          </Heading>

          <p className="hero-subtitle">
            Analyze and lint your Laravel migrations <br /> before they break
            production.
          </p>

          <div className="hero-buttons">
            <Link
              className="button button--primary button--lg"
              to="/docs"
            >
              🚀 Get Started
            </Link>

            <Link
              className="button button--outline button--lg github-button"
              href="https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package"
            >
              ⭐ View on GitHub
            </Link>
          </div>

          <div className="hero-image">
            <img
              src="/sufyan-laravel-migration-lint-package/img/preview.png"
              alt="Laravel Migration Linter Preview"
              width="800"
              height="250"
            />
          </div>
        </section>
      </main>
    </Layout>
  );
}
