import { Box, Image } from "@chakra-ui/react";
import hero from "../assets/velum-hero.png";

export default function Hero() {
  return (
    <Box w="100vw" ml="calc(50% - 50vw)">
      <Image
        src={hero}
        alt="Le Velum - Théâtre Nantais"
        w="100%"
        h={{ base: "100px", md: "600px" }}
        objectFit="cover"
        display="block"
      />
    </Box>
  );
}
