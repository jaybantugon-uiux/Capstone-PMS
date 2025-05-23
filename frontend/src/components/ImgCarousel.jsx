import Carousel from 'react-bootstrap/Carousel';
import BannerImage from '../assets/home-banner-image.jpg';
import BannerImage2 from '../assets/home-banner-image2.jpg';
import BannerImage3 from '../assets/home-banner-image3.jpg';

function ImgCarousel() {
  return (
    <Carousel fade>
      <Carousel.Item>
        <img
          src={BannerImage}
          alt="First slide"
          className="img-carousel"
        />
      </Carousel.Item>
      <Carousel.Item>
        <img
          src={BannerImage2}
          alt="Second slide"
          className="img-carousel"
        />
      </Carousel.Item>
      <Carousel.Item>
        <img
          src={BannerImage3}
          alt="Third slide"
          className="img-carousel"
        />
      </Carousel.Item>
    </Carousel>
  );
}

export default ImgCarousel;
