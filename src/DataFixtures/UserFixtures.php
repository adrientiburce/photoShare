<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /** @var UserPasswordEncoderInterface * */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {

        $faker = Factory::create('fr_FR');


        $user = new User();
        $hash = $this->passwordEncoder->encodePassword($user, 'solene');
        $user->setEmail('solene@gmail.com')
            ->setCreatedAt(new \DateTime())
            ->setPassword($hash);

        $manager->persist($user);


        // create ten users to easily add friends
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $hash = $this->passwordEncoder->encodePassword($user, 'user');
            $user->setPassword($hash)
                ->setEmail($faker->email)
                ->setCreatedAt($faker->dateTimeBetween($startDate = '-5 years', $endDate = 'now', $timezone = null));
            $manager->persist($user);
        }
        $manager->flush();
    }
}
