import React from 'react';
import NavBar from '../components/NavBar.jsx';
import HomeCards from '../components/HomeCards.jsx';
import ImgCarousel from '../components/ImgCarousel.jsx';
import Footer from '../components/Footer.jsx';

const Home = () => {
  return (
    <div className="home-container">
      <NavBar />
      
      <div className="home-background">
        <header className="hero-section">
          <div className="hero-content">
            <h1>Transform Your Space</h1>
            <p>Elegant, timeless interiors tailored for you.</p>
            <button className="cta-btn">Book a Consultation</button>
          </div>
          <div className="hero-image">
            <ImgCarousel />
          </div>
        </header>
        <section className="about-section">
          <h1 className="about-section-title">Our Philosophy</h1>
          <p>We believe in creating spaces that reflect your personality and stand the test of time.</p>
          <div className="values">
            <div className="value">Timeless</div>
            <div className="value">Personalized</div>
            <div className="value">Sustainable</div>
          </div>
        </section>
        <section className="projects-section">
          <h1 className="projects-section-title">Featured Projects</h1>
          <ImgCarousel />
        </section>
        <footer className="footer-section">
          <button className="cta-btn">Let's Talk</button>
          <div className="social-icons">
            {}
          </div>
        </footer>
        <svg className="home-wave" viewBox="0 0 1440 320" preserveAspectRatio="none">
          <path fill="#fff" fillOpacity="1" d="M0,224L60,197.3C120,171,240,117,360,117.3C480,117,600,171,720,197.3C840,224,960,224,1080,197.3C1200,171,1320,117,1380,90.7L1440,64L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path>
        </svg>
      </div>
      <Footer />
    </div>
  );
}

export default Home;