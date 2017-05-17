<?php

namespace AppBundle\Feed;

use AppBundle\Entity\Merchant;
use AppBundle\Entity\Offer;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class Reader
 * @package AppBundle\Feed
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Reader
{
    /**
     * @var EntityManagerInterface
     */
    private $em;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Reads the merchant's feed and creates or update the resulting offers.
     *
     * @param Merchant $merchant
     *
     * @return int The number of created or updated offers.
     */
    public function read(Merchant $merchant)
    {
        $countNew = 0;
        $countUpt = 0;

        // Lire le flux de données du marchand (liste de tableaux associatifs)
        $content = file_get_contents($merchant->getFeedUrl());

        // Convertir les données JSON en tableau
        $feedOffers = json_decode($content, true);
        //var_dump($feedOffers);

        // Pour chaque couple de données "code ean / prix"
        foreach ($feedOffers as $feedOffer) {
            // Trouver le produit correspondant
            $product = $this
                ->em
                ->getRepository('AppBundle:Product')
                ->findOneBy(['eanCode' => $feedOffer['ean_code']]);

            if (!$product) continue;

            // Trouver l'offre correspondant à ce produit et ce marchand
            $offer = $this
                ->em
                ->getRepository('AppBundle:Offer')
                ->findOneBy([
                    'merchant' => $merchant,
                    'product' => $product,
                ]);

            if (!$offer) {
                // créer l'offre
                $offer = new Offer();
                $offer->setMerchant($merchant);
                $offer->setProduct($product);
                $countNew ++;
            } else {
                $countUpt ++;
            }

            // Mettre à jour le prix et la date de mise à jour de l'offre
            // NB : ces deux opérations sont communes à New et Update
            $offer->setPrice($feedOffer['price']);
            $offer->setUpdatedAt(new \DateTime());

            // Enregistrer l'offre
            $this->em->persist($offer);
            $this->em->flush(); 
        }

        // Renvoyer le nombre d'offres
        return [$countNew, $countUpt];
    }
}
