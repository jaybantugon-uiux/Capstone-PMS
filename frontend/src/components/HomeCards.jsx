import React from 'react';
import ImgCarousel from './ImgCarousel.jsx';
import { FiArrowRight } from 'react-icons/fi';
import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';

const cards = [
  {
    key: 1,
    cardClass: 'primary-card',
    titleClass: 'card-title',
    title: 'TRANSFORM YOUR SPACE WITH TIMELESS DESIGN',
    textClass: 'card-text',
    text: `Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
      Vivamus gravida leo ac nibh vestibulum feugiat eget ut elit. 
      Aliquam vel ultricies ex. Quisque ut ultricies ante. 
      Morbi dignissim tortor ante, ut volutpat nunc rutrum in. 
      Praesent vel lorem eu dolor iaculis consectetur eget in arcu. 
      Nam sed ultrices turpis. Cras ornare ex velit, non lacinia enim sodales in. 
      Morbi a mauris eget leo aliquet condimentum.`,
    button: 'Get Started',
    buttonClass: 'card-button',
    buttonIcon: <FiArrowRight />,
    reverse: false,
  },
  {
    key: 2,
    cardClass: 'primary-card reverse-card',
    titleClass: 'card-title reverse-title',
    title: 'OUR PAST PROJECTS',
    textClass: 'card-text reverse-text',
    text: `Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
      Vivamus gravida leo ac nibh vestibulum feugiat eget ut elit. 
      Aliquam vel ultricies ex. Quisque ut ultricies ante. 
      Morbi dignissim tortor ante, ut volutpat nunc rutrum in. 
      Praesent vel lorem eu dolor iaculis consectetur eget in arcu. 
      Nam sed ultrices turpis. Cras ornare ex velit, non lacinia enim sodales in. 
      Morbi a mauris eget leo aliquet condimentum.`,
    button: 'Explore Completed Projects',
    buttonClass: 'card-button',
    buttonIcon: <FiArrowRight className="arrow-right-icon" />,
    reverse: true,
  },
  {
    key: 3,
    cardClass: 'primary-card',
    titleClass: 'card-title',
    title: 'TRANSFORM YOUR SPACE WITH TIMELESS DESIGN',
    textClass: 'card-text',
    text: `Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
      Vivamus gravida leo ac nibh vestibulum feugiat eget ut elit. 
      Aliquam vel ultricies ex. Quisque ut ultricies ante. 
      Morbi dignissim tortor ante, ut volutpat nunc rutrum in. 
      Praesent vel lorem eu dolor iaculis consectetur eget in arcu. 
      Nam sed ultrices turpis. Cras ornare ex velit, non lacinia enim sodales in. 
      Morbi a mauris eget leo aliquet condimentum.`,
    button: 'Get Started',
    buttonClass: 'card-button',
    buttonIcon: <FiArrowRight />,
    reverse: false,
  },
  {
    key: 4,
    cardClass: 'primary-card reverse-card',
    titleClass: 'card-title reverse-title',
    title: 'OUR PAST PROJECTS',
    textClass: 'card-text reverse-text',
    text: `Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
      Vivamus gravida leo ac nibh vestibulum feugiat eget ut elit. 
      Aliquam vel ultricies ex. Quisque ut ultricies ante. 
      Morbi dignissim tortor ante, ut volutpat nunc rutrum in. 
      Praesent vel lorem eu dolor iaculis consectetur eget in arcu. 
      Nam sed ultrices turpis. Cras ornare ex velit, non lacinia enim sodales in. 
      Morbi a mauris eget leo aliquet condimentum.`,
    button: 'Explore Completed Projects',
    buttonClass: 'card-button',
    buttonIcon: <FiArrowRight className="arrow-right-icon" />,
    reverse: true,
  },
];

const HomeCards = () => (
  <div className="home-primary-container">
    {cards.map(card => (
      <Card className={card.cardClass} key={card.key}>
        {card.reverse ? (
          <>
            <div className="primary-card-body">
              <Card.Title className={card.titleClass}>{card.title}</Card.Title>
              <Card.Text className={card.textClass}>{card.text}</Card.Text>
              <Button variant="primary" className={card.buttonClass}>
                {card.button} {card.buttonIcon}
              </Button>
            </div>
            <div className="carousel-container">
              <ImgCarousel />
            </div>
          </>
        ) : (
          <>
            <div className="carousel-container">
              <ImgCarousel />
            </div>
            <div className="primary-card-body">
              <Card.Title className={card.titleClass}>{card.title}</Card.Title>
              <Card.Text className={card.textClass}>{card.text}</Card.Text>
              <Button variant="primary" className={card.buttonClass}>
                {card.button} {card.buttonIcon}
              </Button>
            </div>
          </>
        )}
      </Card>
    ))}
  </div>
);

export default HomeCards;