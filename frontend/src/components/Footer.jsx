import React from 'react';
import { FaMapMarkerAlt, FaEnvelope, FaPhone } from 'react-icons/fa';

const Footer = () => (
  <footer className="custom-footer">
    <div className="footer-col left">
      <div className="footer-brand">Designs R' Us</div>
      <div className="footer-year">2025 ©</div>
    </div>
    <div className="footer-col middle">
      <div className="footer-row">
        <FaMapMarkerAlt className="footer-icon" />
        <span>
          Unit 213-215 Saint Anthony Building 891<br />
          Aurora Boulevard Corner Oxford 1102<br />
          Quezon City, Philippines
        </span>
      </div>
      <div className="footer-row">
        <FaEnvelope className="footer-icon" />
        <span>
          admin@designsrsuph.com<br />
          designs@designsrsuph.com<br />
          projects@designsrsuph.com
        </span>
      </div>
      <div className="footer-row">
        <FaPhone className="footer-icon" />
        <span>
          (2)7001-6357<br />
          Smart: +639088897761<br />
          Sun: +639257680804<br />
          Globe: +639778157277
        </span>
      </div>
    </div>
    <div className="footer-col right">
      <div className="footer-connect-title">
        Connect with us through<br />this website:
      </div>
      <button className="footer-btn">Sign up</button>
      <button className="footer-btn">Login</button>
    </div>
  </footer>
);

export default Footer;