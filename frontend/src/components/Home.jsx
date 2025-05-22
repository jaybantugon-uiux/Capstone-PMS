import React from 'react';
import NavBar from './NavBar.jsx';
import BannerImage from '../assets/home-banner-image.jpg';
import {FiArrowRight} from 'react-icons/fi';
import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';


const Home = () => {
  return (
    <div className="home-container">
      <NavBar />
      <Card className="primary-card">
        <Card.Img variant="top" src={BannerImage} className="card-img" />
        <Card.Body className="primary-card-body">
          <Card.Title className="card-title">
            TRANSFORM YOUR SPACE WITH TIMELESS DESIGN
          </Card.Title>
          <Card.Text className="card-text">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
            Vivamus gravida leo ac nibh vestibulum feugiat eget ut elit. 
            Aliquam vel ultricies ex. Quisque ut ultricies ante. 
            Morbi dignissim tortor ante, ut volutpat nunc rutrum in. 
            Praesent vel lorem eu dolor iaculis consectetur eget in arcu. 
            Nam sed ultrices turpis. Cras ornare ex velit, non lacinia enim sodales in. 
            Morbi a mauris eget leo aliquet condimentum.
          </Card.Text>
          <Button variant="primary" className="card-button">
            Get Started <FiArrowRight/>
          </Button>
        </Card.Body>
      </Card>
    </div>
  );
}

export default Home;
